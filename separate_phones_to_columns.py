#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import pandas as pd

print("قراءة ملف الإكسل...")
df = pd.read_excel('Copy of Employee Contact Data Oct.2025.xlsx')

print(f"شكل البيانات الأصلي: {df.shape}")

# Find the mobile column
mobile_column = df.columns[-1]

# Find the maximum number of phone numbers in a single cell
max_phones = 0
for idx in range(len(df)):
    phone = df.loc[idx, mobile_column]
    if pd.notna(phone) and '\n' in str(phone):
        count = len(str(phone).split('\n'))
        max_phones = max(max_phones, count)

print(f"\nأقصى عدد من الأرقام في خلية واحدة: {max_phones}")

if max_phones > 0:
    # Create new columns for phone numbers
    for i in range(max_phones):
        col_name = f'Mobile_{i+1}' if i > 0 else 'Mobile_1'
        df[col_name] = ''
    
    # Remove the old mobile column temporarily
    df_temp = df.drop(columns=[mobile_column])
    
    print("\nجار فصل الأرقام إلى أعمدة منفصلة...")
    
    for idx in range(len(df)):
        phone = df.loc[idx, mobile_column]
        
        if pd.notna(phone):
            phone_str = str(phone).strip()
            
            if '\n' in phone_str:
                # Multiple numbers - split them
                numbers = [num.strip() for num in phone_str.split('\n') if num.strip()]
                
                # Add numbers to separate columns
                for i, number in enumerate(numbers):
                    col_name = f'Mobile_{i+1}'
                    if i == 0:
                        df_temp.loc[idx, 'Mobile_1'] = number
                    else:
                        df_temp.loc[idx, col_name] = number
            else:
                # Single number
                df_temp.loc[idx, 'Mobile_1'] = phone_str
        
        # Copy other columns
        for col in df.columns:
            if col != mobile_column:
                df_temp.loc[idx, col] = df.loc[idx, col]
    
    df = df_temp
    print(f"تم فصل الأرقام بنجاح!")

# Save the file
output_file = 'Copy of Employee Contact Data Oct.2025_SEPARATED.xlsx'
df.to_excel(output_file, index=False)
print(f"\nتم حفظ الملف: {output_file}")

# Show some examples
print("\nأمثلة على النتيجة:")
print(df.head(10))


