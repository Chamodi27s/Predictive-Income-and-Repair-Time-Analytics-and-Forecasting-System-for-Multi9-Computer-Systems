# predict_api.py

import sys
import joblib
import pandas as pd
import json

# Load trained model
model = joblib.load("time_prediction_model.pkl")

# Get JSON input from PHP
input_json = sys.stdin.read()
data = json.loads(input_json)

# Convert to DataFrame
new_job = pd.DataFrame([data])

# Predict
prediction = model.predict(new_job)

# Return result
print(round(prediction[0], 2))