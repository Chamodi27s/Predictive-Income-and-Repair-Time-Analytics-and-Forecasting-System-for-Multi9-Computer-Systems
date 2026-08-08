import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import OneHotEncoder
from sklearn.pipeline import Pipeline
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.metrics import accuracy_score
import joblib

# 1. Load the dataset
print("Loading dataset...")
df = pd.read_excel("cleaned_parts_required_dataset.xlsx")
df = df.dropna()

# 2. Define Features (X) and Targets (y)
X = df[['Device_Type', 'Item_Model', 'Fault_Description']]
y_parts = df['Parts_Required']
y_cost = df['Cost']

# 3. Split the data
X_train, X_test, y_parts_train, y_parts_test = train_test_split(X, y_parts, test_size=0.2, random_state=42)
_, _, y_cost_train, y_cost_test = train_test_split(X, y_cost, test_size=0.2, random_state=42)

# 4. Preprocessor
preprocessor = ColumnTransformer(
    [
        ('cat', OneHotEncoder(handle_unknown='ignore'), ['Device_Type', 'Item_Model', 'Fault_Description'])
    ]
)

# 5. Train Parts Model (Classifier)
print("Training Parts Model...")
parts_model = Pipeline([
    ('prep', preprocessor),
    ('model', RandomForestClassifier(n_estimators=200, random_state=42))
])
parts_model.fit(X_train, y_parts_train)

# Evaluate Parts Model
pred_parts = parts_model.predict(X_test)
acc = accuracy_score(y_parts_test, pred_parts)
print(f"Parts Model Trained. Accuracy: {acc * 100:.2f}%")

# 6. Train Cost Model (Regressor)
print("Training Cost Model...")
cost_model = Pipeline([
    ('prep', preprocessor),
    ('model', RandomForestRegressor(n_estimators=200, random_state=42))
])
cost_model.fit(X_train, y_cost_train)
print("Cost Model Trained.")

# 7. Save Models
joblib.dump(parts_model, "parts_model.pkl")
joblib.dump(cost_model, "cost_model.pkl")
print("Models saved successfully to parts_model.pkl and cost_model.pkl!")
