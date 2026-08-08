import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import LabelEncoder
from sklearn import metrics
import joblib
import json # අලුතින් එක් කළා

# 1. දත්ත Load කිරීම
file_name = 'Model Train 2 (1).xlsx' 
df = pd.read_excel(file_name)
df = df.dropna(subset=['Duration_Days'])

# 2. Preprocessing
categorical_cols = ['Device_Type', 'Fault_Description', 'Technician', 'Repair_Path', 'Warranty']
encoders = {}
for col in categorical_cols:
    le = LabelEncoder()
    df[col] = le.fit_transform(df[col].astype(str))
    encoders[col] = le 

X = df[categorical_cols]
y = df['Duration_Days']
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 3. Model Training
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# 4. Accuracy ගණනය කිරීම
predictions = model.predict(X_test)
mae = metrics.mean_absolute_error(y_test, predictions)
rmse = np.sqrt(metrics.mean_squared_error(y_test, predictions))
r2 = metrics.r2_score(y_test, predictions)
accuracy = 100 - np.mean(100 * (abs(predictions - y_test) / y_test.replace(0, 1)))

# 5. Stats JSON එකකට Save කිරීම (PHP එකට කියවන්න)
stats = {
    "accuracy": round(accuracy, 2),
    "mae": round(mae, 2),
    "r2": round(r2, 3)
}
with open('model_stats.json', 'w') as f:
    json.dump(stats, f)

# Files Save කිරීම
joblib.dump(model, 'repair_model.pkl')
joblib.dump(encoders, 'label_encoders.pkl')
joblib.dump(X.columns.tolist(), 'feature_names.pkl')

print("Model & Stats Saved Successfully!")