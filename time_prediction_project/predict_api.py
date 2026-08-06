# predict_api.py

import sys
import joblib
import pandas as pd
import json
import warnings
warnings.filterwarnings("ignore", category=UserWarning)

# Load trained models
model_time = joblib.load("time_prediction_model.pkl")
model_parts = joblib.load("parts_model.pkl")
model_cost = joblib.load("cost_model.pkl")

# Get JSON input from PHP
input_json = sys.stdin.read()
data = json.loads(input_json)

# Convert to DataFrame
df = pd.DataFrame([data])

# 1. Predict Time (using all 7 features as expected by time model)
prediction_time = model_time.predict(df)[0]
predicted_days = round(prediction_time, 2)

# 2. Predict Parts and Cost (using only 3 features as expected by new models)
df_new = df[['Device_Type', 'Item_Model', 'Fault_Description']]
predicted_parts = model_parts.predict(df_new)[0]
predicted_cost = model_cost.predict(df_new)[0]

# Output as JSON so PHP can parse it easily
output_data = {
    "days": predicted_days,
    "parts": predicted_parts,
    "cost": round(predicted_cost, 2)
}
print(json.dumps(output_data))