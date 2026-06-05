from flask import Flask, request, jsonify, send_from_directory
import joblib
import pandas as pd
import numpy as np
import re
from datetime import datetime, timedelta
import os

app = Flask(__name__, static_folder=".")

# ====================== LOAD MODELS ======================
model = joblib.load("best_model_nlp.pkl")
encoders = joblib.load("encoders.pkl")
tfidf = joblib.load("tfidf_vectorizer.pkl")

print("✅ Models loaded successfully!")

# ====================== TEXT CLEANING ======================
def clean_text(text):
    text = str(text).lower()
    text = re.sub(r'[^a-z\s]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

# ====================== SERVE FRONTEND ======================
@app.route("/")
def index():
    return send_from_directory(".", "index.html")

# ====================== PREDICT ENDPOINT ======================
@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()

        fault_description = data.get("fault_description", "")
        device_type       = data.get("device_type", "")
        item_model        = data.get("item_model", "")
        technician        = data.get("technician", "")
        repair_path       = data.get("repair_path", "")
        warranty          = data.get("warranty", "")
        solution          = data.get("solution", "")
        date_in_str       = data.get("date_in", "")

        # Parse date
        date_in = datetime.strptime(date_in_str, "%Y-%m-%d")
        year  = date_in.year
        month = date_in.month
        day   = date_in.day

        # ---- Encode categoricals (handle unseen labels gracefully) ----
        categorical_vals = {
            "Device_Type": device_type,
            "Item_Model":  item_model,
            "Technician":  technician,
            "Repair_Path": repair_path,
            "Warranty":    warranty,
            "Solution":    solution,
        }

        encoded = {}
        for col, val in categorical_vals.items():
            le = encoders[col]
            if val in le.classes_:
                encoded[col] = le.transform([val])[0]
            else:
                # Use most-frequent class (index 0) for unseen values
                encoded[col] = 0

        # ---- TF-IDF on fault description ----
        fault_clean = clean_text(fault_description)
        tfidf_vec   = tfidf.transform([fault_clean])
        tfidf_df    = pd.DataFrame(
            tfidf_vec.toarray(),
            columns=[f"fault_{i}" for i in range(tfidf_vec.shape[1])]
        )

        # ---- Build feature row ----
        cat_df = pd.DataFrame([{
            "Device_Type": encoded["Device_Type"],
            "Item_Model":  encoded["Item_Model"],
            "Technician":  encoded["Technician"],
            "Repair_Path": encoded["Repair_Path"],
            "Warranty":    encoded["Warranty"],
            "Solution":    encoded["Solution"],
            "Year":  year,
            "Month": month,
            "Day":   day,
        }])

        X = pd.concat([cat_df, tfidf_df], axis=1)

        # ---- Predict ----
        predicted_days = int(round(model.predict(X)[0]))
        predicted_days = max(0, predicted_days)   # clamp negatives

        date_out = date_in + timedelta(days=predicted_days)

        return jsonify({
            "success": True,
            "predicted_days": predicted_days,
            "date_in":  date_in.strftime("%Y-%m-%d"),
            "date_out": date_out.strftime("%Y-%m-%d"),
        })

    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 400

# ====================== DROPDOWN OPTIONS ENDPOINT ======================
@app.route("/options", methods=["GET"])
def options():
    """Return only the dropdown fields (Warranty & Technician)."""
    return jsonify({
        "Warranty":   sorted(set(v.title() for v in encoders["Warranty"].classes_)),
        "Technician": sorted(set(v.title() for v in encoders["Technician"].classes_)),
    })

if __name__ == "__main__":
    app.run(debug=True, port=5000)