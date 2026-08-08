import joblib
import pandas as pd

# Save කරපු දේවල් ආපහු load කරගැනීම
model = joblib.load('repair_model.pkl')
encoders = joblib.load('label_encoders.pkl')
features = joblib.load('feature_names.pkl')

def get_prediction():
    print("\n--- Multi9 Repair Time Predictor ---")
    
    # පාරිභෝගිකයාගේ දත්ත ඇතුළත් කරන්න
    # (මතක තබා ගන්න: Excel එකේ තිබුණු විදිහටම අකුරු ටයිප් කරන්න - උදා: Laptop, Desktop PC)
    data = {
        'Device_Type': input("Device Type එක (eg: Laptop): "),
        'Fault_Description': input("ලෙඩේ (eg: No Power): "),
        'Technician': input("Technician ගේ නම: "),
        'Repair_Path': input("Repair Path (In-House/Agent): "),
        'Warranty': input("Warranty (Yes/No): ")
    }

    # අකුරු ටික ඉලක්කම් වලට හරවනවා (Encoding)
    input_df = pd.DataFrame([data])
    for col, le in encoders.items():
        try:
            input_df[col] = le.transform(input_df[col].astype(str))
        except:
            print(f"Error: {col} එකට දුන්න අගය පද්ධතිය අඳුනන්නේ නැහැ!")
            return

    # Prediction එක කරනවා
    prediction = model.predict(input_df)
    print(f"\n✅ මේ වැඩේ ඉවර කරන්න දින {prediction[0]:.1f} ක් විතර යයි.")

# Run කිරීම
get_prediction()