import sys
import joblib
import pandas as pd
import os

# ෆයිල් එක තියෙන තැන හරියටම ගන්න
base_path = os.path.dirname(os.path.abspath(__file__))

# Model සහ Encoders load කිරීම
model = joblib.load(os.path.join(base_path, 'repair_model.pkl'))
encoders = joblib.load(os.path.join(base_path, 'label_encoders.pkl'))

# PHP වලින් එවනු ලබන දත්ත ලබා ගැනීම
# sys.argv[1] සිට sys.argv[5] දක්වා පිළිවෙලින් දත්ත ලැබෙයි
try:
    input_data = {
        'Device_Type': sys.argv[1],
        'Fault_Description': sys.argv[2],
        'Technician': sys.argv[3],
        'Repair_Path': sys.argv[4],
        'Warranty': sys.argv[5]
    }

    input_df = pd.DataFrame([input_data])

    # අකුරු ඉලක්කම් වලට හරවා ගැනීම (Encoding)
    for col, le in encoders.items():
        if col in input_df.columns:
            # අලුත් දත්තයක් ආවොත් (Unseen labels) එය handle කිරීම
            val = str(input_df[col][0])
            if val in le.classes_:
                input_df[col] = le.transform([val])
            else:
                input_df[col] = 0 

    # අනාවැකිය (Prediction)
    prediction = model.predict(input_df)
    print(round(prediction[0], 2)) # මෙහි ප්‍රතිඵලය පමණක් PHP එකට ලැබේ

except Exception as e:
    print(f"Error: {str(e)}")