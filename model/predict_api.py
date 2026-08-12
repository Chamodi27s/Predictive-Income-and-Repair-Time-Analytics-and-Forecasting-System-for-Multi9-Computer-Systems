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
CORS(app)


# =========================================================
# MODEL PATHS
# =========================================================

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

TIME_MODEL_PATH = os.path.join(
    BASE_DIR,
    "best_model_nlp.pkl"
)

ENCODERS_PATH = os.path.join(
    BASE_DIR,
    "encoders.pkl"
)

TFIDF_PATH = os.path.join(
    BASE_DIR,
    "tfidf_vectorizer.pkl"
)

COST_MODEL_PATH = os.path.join(
    BASE_DIR,
    "cost_model.pkl"
)


# =========================================================
# LOAD MODELS
# =========================================================

try:
    time_model = joblib.load(TIME_MODEL_PATH)
    encoders = joblib.load(ENCODERS_PATH)
    tfidf = joblib.load(TFIDF_PATH)
    cost_model = joblib.load(COST_MODEL_PATH)

    print("Time and cost models loaded successfully.")

except Exception as error:
    print(f"Model loading error: {error}")
    raise


# =========================================================
# HELPER FUNCTIONS
# =========================================================

def clean_text(text):
    """
    Clean fault-description text before applying TF-IDF.
    """

    text = str(text or "").lower()
    text = re.sub(r"[^a-z\s]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()

    return text


def safe_encode(column_name, value):
    """
    Encode categorical values used by the time model.

    It first checks the exact value and then performs a
    case-insensitive check. Unknown values fall back to 0.
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
    class_mapping = {
        str(class_value).strip().lower(): class_value
        for class_value in label_encoder.classes_
    }

    matched_value = class_mapping.get(
        input_value.lower()
    )

    if matched_value is not None:
        return int(
            label_encoder.transform([matched_value])[0]
        )

    # Unknown value fallback
    return 0


def get_encoder_options(column_name):
    """
    Return available categorical values.
    """

    if column_name not in encoders:
        return []

    return sorted(
        [
            str(value)
            for value in encoders[column_name].classes_
        ],
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
        "message": (
            "Multi9 Repair Time and Cost Prediction API "
            "is running."
        ),
        "endpoints": {
            "time_prediction": "/predict",
            "cost_prediction": "/predict-cost",
            "options": "/options",
            "health": "/health"
        }
    }), 200


# =========================================================
# TIME PREDICTION ENDPOINT
# =========================================================

@app.route("/predict", methods=["POST"])
def predict_time():
    try:
        data = request.get_json(silent=True)

        if not isinstance(data, dict):
            return jsonify({
                "success": False,
                "error": (
                    "Request body must contain valid JSON data."
                )
            }), 400

        fault_description = str(
            data.get("fault_description", "")
        ).strip()

        device_type = str(
            data.get("device_type", "")
        ).strip()

        item_model = str(
            data.get("item_model", "")
        ).strip()

        technician = str(
            data.get("technician", "")
        ).strip()

        repair_path = str(
            data.get("repair_path", "Carry-In")
        ).strip()

        warranty = str(
            data.get("warranty", "")
        ).strip()

        solution = str(
            data.get("solution", "")
        ).strip()

        date_in_str = str(
            data.get(
                "date_in",
                datetime.today().strftime("%Y-%m-%d")
            )
        ).strip()

        # Validate the input date
        try:
            date_in = datetime.strptime(
                date_in_str,
                "%Y-%m-%d"
            )

        except ValueError:
            return jsonify({
                "success": False,
                "error": (
                    "Invalid date format. Use YYYY-MM-DD."
                )
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

        # NLP TF-IDF conversion
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

        # Combine all input features
        prediction_data = pd.concat(
            [
                categorical_df.reset_index(drop=True),
                tfidf_df.reset_index(drop=True)
            ],
            axis=1
        )

        # Ensure feature order matches training data
        if hasattr(time_model, "feature_names_in_"):
            expected_columns = list(
                time_model.feature_names_in_
            )

            prediction_data = prediction_data.reindex(
                columns=expected_columns,
                fill_value=0
            )

        prediction = time_model.predict(
            prediction_data
        )[0]

        predicted_days = int(
            round(float(prediction))
        )

        predicted_days = max(
            0,
            predicted_days
        )

        predicted_date_out = date_in + timedelta(
            days=predicted_days
        )

        return jsonify({
            "success": True,
            "prediction_type": "repair_time",
            "predicted_days": predicted_days,
            "date_in": date_in.strftime("%Y-%m-%d"),
            "date_out": predicted_date_out.strftime(
                "%Y-%m-%d"
            )
        }), 200

    except Exception as error:
        print(f"Time prediction error: {error}")

        return jsonify({
            "success": False,
            "error": str(error)
        }), 500


# =========================================================
# COST PREDICTION ENDPOINT
# =========================================================

@app.route("/predict-cost", methods=["POST"])
def predict_cost():
    try:
        data = request.get_json(silent=True)

        if not isinstance(data, dict):
            return jsonify({
                "success": False,
                "error": (
                    "Request body must contain valid JSON data."
                )
            }), 400

        # Accept lowercase API names or dataset column names
        device_type = str(
            data.get("device_type")
            or data.get("Device_Type")
            or ""
        ).strip()

        item_model = str(
            data.get("item_model")
            or data.get("Item_Model")
            or ""
        ).strip()

        fault_description = str(
            data.get("fault_description")
            or data.get("Fault_Description")
            or ""
        ).strip()

        missing_fields = []

        if not device_type:
            missing_fields.append("device_type")

        if not item_model:
            missing_fields.append("item_model")

        if not fault_description:
            missing_fields.append(
                "fault_description"
            )

        if missing_fields:
            return jsonify({
                "success": False,
                "error": "Required fields are missing.",
                "missing_fields": missing_fields
            }), 400

        # Exact columns used for training the cost model
        cost_input = pd.DataFrame([{
            "Device_Type": device_type,
            "Item_Model": item_model,
            "Fault_Description": fault_description
        }])

        prediction = cost_model.predict(
            cost_input
        )[0]

        predicted_cost = float(prediction)

        # Prevent negative costs
        predicted_cost = max(
            0.0,
            predicted_cost
        )

        return jsonify({
            "success": True,
            "prediction_type": "repair_cost",
            "device_type": device_type,
            "item_model": item_model,
            "fault_description": fault_description,
            "predicted_cost": round(
                predicted_cost,
                2
            ),
            "formatted_cost": "Rs. {:,.2f}".format(
                predicted_cost
            )
        }), 200

    except Exception as error:
        print(f"Cost prediction error: {error}")

        return jsonify({
            "success": False,
            "error": str(error)
        }), 500


# =========================================================
# OPTIONS ENDPOINT
# =========================================================

@app.route("/options", methods=["GET"])
def options():
    return jsonify({
        "success": True,
        "Warranty": get_encoder_options(
            "Warranty"
        ),
        "Technician": get_encoder_options(
            "Technician"
        ),
        "Device_Type": get_encoder_options(
            "Device_Type"
        ),
        "Item_Model": get_encoder_options(
            "Item_Model"
        ),
        "Repair_Path": get_encoder_options(
            "Repair_Path"
        ),
        "Solution": get_encoder_options(
            "Solution"
        )
    }), 200


# =========================================================
# HEALTH ENDPOINT
# =========================================================

@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "success": True,
        "status": "ok",
        "time_model_loaded": time_model is not None,
        "cost_model_loaded": cost_model is not None
    }), 200


# =========================================================
# RUN LOCALLY
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