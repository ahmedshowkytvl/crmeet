#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import pandas as pd
import openpyxl
from openpyxl.styles import Alignment

print("قراءة ملف الإكسل...")
df = pd.read_excel('Copy of Employee Contact Data Oct.2025.xlsx')

print(f"عدد الصفوف: {len(df)}")

# Find the mobile column
mobile_column = df.columns[-1]

print(f"\nاسم عمود الهواتف: {mobile_column}")

# Check if there are numbers without leading 0 that need fixing
print("\nجار البحث عن الأرقام التي تحتاج إصلاح...")

fixed_count = 0
for idx in range(len(df)):
    if idx == 0:  # Skip header
        continue
    
    phone = df.loc[idx, mobile_column]
    
    if pd.notna(phone):
        phone_str = str(phone)
        
        # Check if contains \n (multiple numbers)
        if '\n' in phone_str:
            numbers = phone_str.split('\n')
            fixed_numbers = []
            
            for number in numbers:
                number = number.strip()
                # Check if it doesn't start with 0 and is 10 digits
                if number and number.isdigit():
                    if not number.startswith('0') and len(number) == 10:
                        number = '0' + number
                        fixed_count += 1
                    elif not number.startswith('0') and len(number) == 9:
                        number = '0' + number
                        fixed_count += 1
                fixed_numbers.append(number)
            
            df.loc[idx, mobile_column] = '\n'.join(fixed_numbers)
        else:
            # Single number
            if phone_str and phone_str.isdigit():
                if not phone_str.startswith('0') and len(phone_str) == 10:
                    df.loc[idx, mobile_column] = '0' + phone_str
                    fixed_count += 1
                elif not phone_str.startswith('0') and len(phone_str) == 9:
                    df.loc[idx, mobile_column] = '0' + phone_str
                    fixed_count += 1

print(f"تم إصلاح {fixed_count} رقم")

# Save using openpyxl to ensure proper handling of line breaks
print("\nجار حفظ الملف باستخدام openpyxl...")
output_file = 'Copy of Employee Contact Data Oct.2025_FIXED.xlsx'

with pd.ExcelWriter(output_file, engine='openpyxl') as writer:
    df.to_excel(writer, index=False)
    worksheet = writer.sheets['Sheet1']
    
    # Set wrap text and top alignment for mobile column
    col_idx = df.columns.get_loc(mobile_column) + 1
    for row_idx in range(1, len(df) + 1):
        cell = worksheet.cell(row=row_idx, column=col_idx)
        if cell.value and '\n' in str(cell.value):
            cell.alignment = Alignment(vertical='top', wrap_text=True)

print(f"تم حفظ الملف بنجاح: {output_file}")

# Show some examples
print("\nأمثلة على النتيجة:")
print(df[mobile_column].head(10))

