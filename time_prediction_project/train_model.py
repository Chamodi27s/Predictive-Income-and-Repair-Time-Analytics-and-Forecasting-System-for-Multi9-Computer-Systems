import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import r2_score, mean_absolute_error, mean_squared_error
import joblib

# 1️⃣ Load Dataset
df = pd.read_excel("Model Train 2.xlsx")

print("Dataset Preview:")
print(df.head())

# 2️⃣ Remove rows where target is missing
df = df.dropna(subset=["Duration_Days"])

# 3️⃣ Define Features (X) and Target (y)
X = df.drop(["Duration_Days", "Job_ID", "Date_In", "Date_Out"], axis=1)
y = df["Duration_Days"]

# 4️⃣ Identify categorical columns
categorical_cols = X.select_dtypes(include=["object", "string"]).columns

# 5️⃣ Preprocessing (One Hot Encoding)
preprocessor = ColumnTransformer(
    transformers=[
        ("cat", OneHotEncoder(handle_unknown="ignore"), categorical_cols)
    ],
    remainder="passthrough"
)

# 6️⃣ Create Model Pipeline
model = Pipeline(steps=[
    ("preprocessor", preprocessor),
    ("regressor", RandomForestRegressor(n_estimators=100, random_state=42))
])

# 7️⃣ Train-Test Split
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42
)

# 8️⃣ Train Model
model.fit(X_train, y_train)

# 9️⃣ Predictions
y_pred = model.predict(X_test)

# 🔟 Evaluation
r2 = r2_score(y_test, y_pred)
mae = mean_absolute_error(y_test, y_pred)
mse = mean_squared_error(y_test, y_pred)
rmse = np.sqrt(mse)

print("\nModel Evaluation:")
print("R2 Score:", r2)
print("MAE:", mae)
print("RMSE:", rmse)

# 1️⃣1️⃣ Cross Validation
cv_scores = cross_val_score(model, X, y, cv=5, scoring="r2")
print("\nCross Validation R2 Scores:", cv_scores)
print("Average CV R2:", cv_scores.mean())

# 1️⃣2️⃣ Save Model
joblib.dump(model, "time_prediction_model.pkl")

print("\nModel Saved Successfully!")