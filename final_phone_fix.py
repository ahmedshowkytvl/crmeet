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
phone_lists = []
for idx in range(len(df)):
    phone = df.loc[idx, mobile_column]
    phones = []
    if pd.notna(phone):
        phone_str = str(phone).strip()
        if '\n' in phone_str:
            phones = [num.strip() for num in phone_str.split('\n') if num.strip()]
        else:
            if phone_str:
                phones = [phone_str]
    
    max_phones = max(max_phones, len(phones))
    phone_lists.append(phones)

print(f"أقصى عدد من الأرقام في خلية واحدة: {max_phones}")

# Create new columns for phone numbers
for i in range(max_phones):
    col_name = f'Mobile_{i+1}'
    df[col_name] = ''

print(f"\nتم إنشاء {max_phones} عمود للأرقام")

# Fill the new columns
for idx in range(len(df)):
    phones = phone_lists[idx]
    for i, phone in enumerate(phones):
        col_name = f'Mobile_{i+1}'
        df.loc[idx, col_name] = phone

# Remove the old mobile column
df = df.drop(columns=[mobile_column])

# Save the file
output_file = 'Copy of Employee Contact Data Oct.2025_SEPARATED.xlsx'
df.to_excel(output_file, index=False)
print(f"\nتم حفظ الملف: {output_file}")

# Show some examples
print("\nأمثلة على النتيجة:")
mobile_cols = [col for col in df.columns if col.startswith('Mobile_')]
print(df[mobile_cols].head(10))


