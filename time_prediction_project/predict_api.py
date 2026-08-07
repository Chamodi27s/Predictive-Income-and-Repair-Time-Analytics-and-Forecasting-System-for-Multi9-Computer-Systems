# predict_api.py

import sys
import os
import joblib
import pandas as pd
import json
import warnings
from difflib import get_close_matches

warnings.filterwarnings("ignore", category=UserWarning)

# Load trained models
model_time = joblib.load("time_prediction_model.pkl")
model_parts = joblib.load("parts_model.pkl")
model_cost = joblib.load("cost_model.pkl")

# Load historical Excel datasets for exact matching
df_dataset = None
df_time_dataset = None

try:
    if os.path.exists("cleaned_parts_required_dataset.xlsx"):
        df_dataset = pd.read_excel("cleaned_parts_required_dataset.xlsx")
except Exception:
    df_dataset = None

try:
    if os.path.exists("Model Train 2.xlsx"):
        df_time_dataset = pd.read_excel("Model Train 2.xlsx")
except Exception:
    df_time_dataset = None

# Extract known categories from cost model pipeline
try:
    known_models = list(model_cost.named_steps['prep'].transformers_[0][1].categories_[1])
except Exception:
    known_models = []

MODEL_ALIASES = {
    'core 2 duo': 'Core 2 Duo PC',
    'core 2 duo pc': 'Core 2 Duo PC',
    'core i3': 'Core i3 PC',
    'core i5': 'Core i5 PC',
    'core i7': 'Core i7 PC'
}

def normalize_model(val):
    if not val or not isinstance(val, str):
        return val
    clean = val.strip().lower()
    if clean in MODEL_ALIASES:
        return MODEL_ALIASES[clean]
    if clean + ' pc' in MODEL_ALIASES:
        return MODEL_ALIASES[clean + ' pc']
    if known_models:
        matches = get_close_matches(val, known_models, n=1, cutoff=0.7)
        if matches:
            return matches[0]
    return val

# Get JSON input from PHP
input_json = sys.stdin.read()
data = json.loads(input_json)

# Normalize Item_Model in input data
if "Item_Model" in data:
    data["Item_Model"] = normalize_model(data["Item_Model"])

# Convert to DataFrame
df = pd.DataFrame([data])

# 1. Check Exact Duration Days match from Model Train 2.xlsx first
exact_days = None
if df_time_dataset is not None:
    try:
        dev = str(data.get("Device_Type", "")).strip().lower()
        mod = str(data.get("Item_Model", "")).strip().lower()
        flt = str(data.get("Fault_Description", "")).strip().lower()

        matches_time = df_time_dataset[
            (df_time_dataset['Device_Type'].astype(str).str.strip().str.lower() == dev) &
            (df_time_dataset['Item_Model'].astype(str).str.strip().str.lower() == mod) &
            (df_time_dataset['Fault_Description'].astype(str).str.strip().str.lower() == flt)
        ]

        if not matches_time.empty:
            exact_days = float(matches_time.iloc[0]['Duration_Days'])
    except Exception:
        pass

if exact_days is not None:
    predicted_days = round(exact_days, 2)
else:
    prediction_time = model_time.predict(df)[0]
    predicted_days = round(prediction_time, 2)

# 2. Check Exact Historical Cost & Parts Matching Layer
predicted_parts = None
predicted_cost = None

if df_dataset is not None:
    try:
        dev = str(data.get("Device_Type", "")).strip().lower()
        mod = str(data.get("Item_Model", "")).strip().lower()
        flt = str(data.get("Fault_Description", "")).strip().lower()

        matches = df_dataset[
            (df_dataset['Device_Type'].astype(str).str.strip().str.lower() == dev) &
            (df_dataset['Item_Model'].astype(str).str.strip().str.lower() == mod) &
            (df_dataset['Fault_Description'].astype(str).str.strip().str.lower() == flt)
        ]

        if not matches.empty:
            exact_row = matches.iloc[0]
            predicted_cost = float(exact_row['Cost'])
            predicted_parts = str(exact_row['Parts_Required'])
    except Exception:
        pass

# Fall back to ML model if no exact match found
if predicted_cost is None or predicted_parts is None:
    df_new = df[['Device_Type', 'Item_Model', 'Fault_Description']]
    predicted_parts = model_parts.predict(df_new)[0]
    predicted_cost = model_cost.predict(df_new)[0]

# Output as JSON so PHP can parse it easily
output_data = {
    "days": predicted_days,
    "parts": predicted_parts,
    "cost": round(float(predicted_cost), 2)
}
print(json.dumps(output_data))