import joblib
import pandas as pd

# 1️⃣ Load saved model
model = joblib.load("time_prediction_model.pkl")

# 2️⃣ New job data (manually add values)
new_job = pd.DataFrame({
    "Device_Type": ["Desktop PC"],
    "Item_Model": ["Core i5 Desktop"],
    "Fault_Description": ["No Power"],
    "Technician": ["Nimales"],
    "Repair_Path": ["In-House"],
    "Warranty": ["No"],
    "Solution": ["Power Supply Replacement"]
})

# 3️⃣ Predict
prediction = model.predict(new_job)

print("Predicted Repair Days:", round(prediction[0], 2))