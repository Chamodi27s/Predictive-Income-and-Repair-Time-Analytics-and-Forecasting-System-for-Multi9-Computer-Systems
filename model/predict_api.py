from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import re
from datetime import datetime, timedelta
import os

app = Flask(__name__)
CORS(app)  # Allow PHP (localhost) to call this API

# ── Load models once at startup ──────────────────────────────────────────────
BASE_DIR = os.path.dirname(os.path.abspath(__file__))  # model/ folder

model    = joblib.load(os.path.join(BASE_DIR, "best_model_nlp.pkl"))
encoders = joblib.load(os.path.join(BASE_DIR, "encoders.pkl"))
tfidf    = joblib.load(os.path.join(BASE_DIR, "tfidf_vectorizer.pkl"))
print("✅ Models loaded — API ready on http://localhost:5001")

# ── Helpers ──────────────────────────────────────────────────────────────────
def clean_text(text):
    text = str(text).lower()
    text = re.sub(r'[^a-z\s]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def safe_encode(col, val):
    """Label-encode a value; fall back to 0 for unseen labels."""
    le = encoders[col]
    return int(le.transform([val])[0]) if val in le.classes_ else 0

# ── Predict endpoint ─────────────────────────────────────────────────────────
@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json(force=True)

        # Required fields
        fault_description = data.get("fault_description", "")
        device_type       = data.get("device_type", "")
        item_model        = data.get("item_model", "")
        technician        = data.get("technician", "")
        repair_path       = data.get("repair_path", "Carry-In")   # default
        warranty          = data.get("warranty", "")
        solution          = data.get("solution", "")
        date_in_str       = data.get("date_in", datetime.today().strftime("%Y-%m-%d"))

        date_in = datetime.strptime(date_in_str, "%Y-%m-%d")

        # Categorical encoding
        cat_df = pd.DataFrame([{
            "Device_Type": safe_encode("Device_Type", device_type),
            "Item_Model":  safe_encode("Item_Model",  item_model),
            "Technician":  safe_encode("Technician",  technician),
            "Repair_Path": safe_encode("Repair_Path", repair_path),
            "Warranty":    safe_encode("Warranty",    warranty),
            "Solution":    safe_encode("Solution",    solution),
            "Year":        date_in.year,
            "Month":       date_in.month,
            "Day":         date_in.day,
        }])

        # TF-IDF
        tfidf_vec = tfidf.transform([clean_text(fault_description)])
        tfidf_df  = pd.DataFrame(
            tfidf_vec.toarray(),
            columns=[f"fault_{i}" for i in range(tfidf_vec.shape[1])]
        )

        X = pd.concat([cat_df, tfidf_df], axis=1)

        predicted_days = max(0, int(round(model.predict(X)[0])))
        date_out       = date_in + timedelta(days=predicted_days)

        return jsonify({
            "success":        True,
            "predicted_days": predicted_days,
            "date_in":        date_in.strftime("%Y-%m-%d"),
            "date_out":       date_out.strftime("%Y-%m-%d"),
        })

    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 400


# ── Dropdown options endpoint ────────────────────────────────────────────────
@app.route("/options", methods=["GET"])
def options():
    return jsonify({
        "Warranty":    sorted(v.title() for v in encoders["Warranty"].classes_),
        "Technician":  sorted(v.title() for v in encoders["Technician"].classes_),
        "Device_Type": sorted(v.title() for v in encoders["Device_Type"].classes_),
        "Solution":    sorted(v.title() for v in encoders["Solution"].classes_),
    })


# ── Health check ─────────────────────────────────────────────────────────────
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


if __name__ == "__main__":
    app.run(debug=False, port=5001)
