from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import re
from datetime import datetime, timedelta
import os


# =========================================================
# FLASK APPLICATION
# =========================================================

app = Flask(__name__)

# Demo/free hosting සඳහා සියලු origins allow කරයි.
# Production එකේදී InfinityFree domain එකට පමණක් restrict කරන්න.
CORS(app)


# =========================================================
# LOAD TRAINED MODEL FILES
# =========================================================

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

MODEL_PATH = os.path.join(BASE_DIR, "best_model_nlp.pkl")
ENCODERS_PATH = os.path.join(BASE_DIR, "encoders.pkl")
TFIDF_PATH = os.path.join(BASE_DIR, "tfidf_vectorizer.pkl")


try:
    model = joblib.load(MODEL_PATH)
    encoders = joblib.load(ENCODERS_PATH)
    tfidf = joblib.load(TFIDF_PATH)

    print("Models loaded successfully.")

except Exception as error:
    print(f"Error loading model files: {error}")
    raise


# =========================================================
# HELPER FUNCTIONS
# =========================================================

def clean_text(text):
    """
    Clean the fault description before applying TF-IDF.
    """

    text = str(text or "").lower()
    text = re.sub(r"[^a-z\s]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()

    return text


def safe_encode(column_name, value):
    """
    Encode categorical values.

    First checks the exact value.
    Then performs a case-insensitive check.
    Returns 0 when the value is not found.
    """

    if column_name not in encoders:
        raise ValueError(
            f"Encoder not found for column: {column_name}"
        )

    label_encoder = encoders[column_name]
    input_value = str(value or "").strip()

    # Exact match
    if input_value in label_encoder.classes_:
        return int(
            label_encoder.transform([input_value])[0]
        )

    # Case-insensitive match
    class_map = {
        str(class_value).strip().lower(): class_value
        for class_value in label_encoder.classes_
    }

    matched_value = class_map.get(input_value.lower())

    if matched_value is not None:
        return int(
            label_encoder.transform([matched_value])[0]
        )

    # Unknown category fallback
    return 0


def get_encoder_options(column_name):
    """
    Return original encoder values without changing their case.
    """

    if column_name not in encoders:
        return []

    return sorted(
        [str(value) for value in encoders[column_name].classes_],
        key=str.lower
    )


# =========================================================
# HOME ENDPOINT
# =========================================================

@app.route("/", methods=["GET"])
def home():
    return jsonify({
        "success": True,
        "status": "running",
        "message": "Repair Time Prediction API is running"
    }), 200


# =========================================================
# HEALTH CHECK ENDPOINT
# =========================================================

@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "success": True,
        "status": "ok",
        "model_loaded": True
    }), 200


# =========================================================
# DROPDOWN OPTIONS ENDPOINT
# =========================================================

@app.route("/options", methods=["GET"])
def options():
    return jsonify({
        "success": True,
        "Warranty": get_encoder_options("Warranty"),
        "Technician": get_encoder_options("Technician"),
        "Device_Type": get_encoder_options("Device_Type"),
        "Item_Model": get_encoder_options("Item_Model"),
        "Repair_Path": get_encoder_options("Repair_Path"),
        "Solution": get_encoder_options("Solution")
    }), 200


# =========================================================
# PREDICTION ENDPOINT
# =========================================================

@app.route("/predict", methods=["POST"])
def predict():
    try:
        # Read JSON request
        data = request.get_json(silent=True)

        if not isinstance(data, dict):
            return jsonify({
                "success": False,
                "error": "Request body must contain valid JSON data."
            }), 400

        # Get request values
        fault_description = data.get(
            "fault_description", ""
        )

        device_type = data.get(
            "device_type", ""
        )

        item_model = data.get(
            "item_model", ""
        )

        technician = data.get(
            "technician", ""
        )

        repair_path = data.get(
            "repair_path", "Carry-In"
        )

        warranty = data.get(
            "warranty", ""
        )

        solution = data.get(
            "solution", ""
        )

        date_in_str = data.get("date_in")

        if not date_in_str:
            date_in_str = datetime.today().strftime(
                "%Y-%m-%d"
            )

        date_in_str = str(date_in_str).strip()

        # Validate and parse date
        try:
            date_in = datetime.strptime(
                date_in_str,
                "%Y-%m-%d"
            )

        except ValueError:
            return jsonify({
                "success": False,
                "error": "Invalid date format. Use YYYY-MM-DD."
            }), 400

        # Encode categorical values
        categorical_data = {
            "Device_Type": safe_encode(
                "Device_Type",
                device_type
            ),

            "Item_Model": safe_encode(
                "Item_Model",
                item_model
            ),

            "Technician": safe_encode(
                "Technician",
                technician
            ),

            "Repair_Path": safe_encode(
                "Repair_Path",
                repair_path
            ),

            "Warranty": safe_encode(
                "Warranty",
                warranty
            ),

            "Solution": safe_encode(
                "Solution",
                solution
            ),

            "Year": date_in.year,
            "Month": date_in.month,
            "Day": date_in.day
        }

        categorical_df = pd.DataFrame([
            categorical_data
        ])

        # Clean and vectorize fault description
        cleaned_fault = clean_text(
            fault_description
        )

        tfidf_vector = tfidf.transform([
            cleaned_fault
        ])

        tfidf_df = pd.DataFrame(
            tfidf_vector.toarray(),
            columns=[
                f"fault_{index}"
                for index in range(
                    tfidf_vector.shape[1]
                )
            ]
        )

        # Combine categorical and TF-IDF features
        prediction_data = pd.concat(
            [
                categorical_df.reset_index(drop=True),
                tfidf_df.reset_index(drop=True)
            ],
            axis=1
        )

        # Ensure feature names and order match training data
        if hasattr(model, "feature_names_in_"):
            expected_columns = list(
                model.feature_names_in_
            )

            prediction_data = prediction_data.reindex(
                columns=expected_columns,
                fill_value=0
            )

        # Make prediction
        prediction = model.predict(
            prediction_data
        )[0]

        predicted_days = int(
            round(float(prediction))
        )

        # Prevent negative prediction
        predicted_days = max(
            0,
            predicted_days
        )

        predicted_date_out = date_in + timedelta(
            days=predicted_days
        )

        return jsonify({
            "success": True,
            "predicted_days": predicted_days,
            "date_in": date_in.strftime("%Y-%m-%d"),
            "date_out": predicted_date_out.strftime(
                "%Y-%m-%d"
            )
        }), 200

    except Exception as error:
        print(f"Prediction error: {error}")

        return jsonify({
            "success": False,
            "error": str(error)
        }), 500


# =========================================================
# RUN APPLICATION LOCALLY
# =========================================================

if __name__ == "__main__":
    port = int(
        os.environ.get("PORT", 5001)
    )

    app.run(
        host="0.0.0.0",
        port=port,
        debug=False
    )