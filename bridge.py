import sys
import joblib
import pandas as pd
import os


base_path = os.path.dirname(os.path.abspath(__file__))


model = joblib.load(os.path.join(base_path, 'repair_model.pkl'))
encoders = joblib.load(os.path.join(base_path, 'label_encoders.pkl'))



try:
    input_data = {
        'Device_Type': sys.argv[1],
        'Fault_Description': sys.argv[2],
        'Technician': sys.argv[3],
        'Repair_Path': sys.argv[4],
        'Warranty': sys.argv[5]
    }

    input_df = pd.DataFrame([input_data])

    
    for col, le in encoders.items():
        if col in input_df.columns:
            
            val = str(input_df[col][0])
            if val in le.classes_:
                input_df[col] = le.transform([val])
            else:
                input_df[col] = 0 


    prediction = model.predict(input_df)
    print(round(prediction[0], 2)) 

except Exception as e:
    print(f"Error: {str(e)}")