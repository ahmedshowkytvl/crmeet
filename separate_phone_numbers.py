#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import pandas as pd

print("قراءة ملف الإكسل...")
df = pd.read_excel('Copy of Employee Contact Data Oct.2025.xlsx')

print(f"شكل البيانات قبل المعالجة: {df.shape}")

# Find the mobile column
mobile_column = df.columns[-1]

# Create a list to store expanded rows
expanded_rows = []

print("\nجار معالجة الصفوف وفصل الأرقام...")

for idx, row in df.iterrows():
    phone = row[mobile_column]
    
    if pd.isna(phone):
        # إذا لم يكن هناك رقم، أضف الصف كما هو
        expanded_rows.append(row.to_dict())
    else:
        phone_str = str(phone).strip()
        
        # فصل الأرقام إذا كان هناك فاصل \n
        if '\n' in phone_str:
            numbers = [num.strip() for num in phone_str.split('\n') if num.strip()]
            
            # إنشاء صف منفصل لكل رقم
            for i, number in enumerate(numbers):
                new_row = row.to_dict()
                
                # إذا كان الرقم الأول، احتفظ بالباقي
                if i == 0:
                    new_row[mobile_column] = number
                else:
                    new_row[mobile_column] = number
                    # أضف صف جديد للأرقام الإضافية
                
                expanded_rows.append(new_row)
        else:
            # رقم واحد فقط
            expanded_rows.append(row.to_dict())

# Create new dataframe
new_df = pd.DataFrame(expanded_rows)

print(f"شكل البيانات بعد المعالجة: {new_df.shape}")
print(f"عدد الصفوف الأصلي: {len(df)}")
print(f"عدد الصفوف بعد التوسع: {len(new_df)}")

# Save the file
output_file = 'Copy of Employee Contact Data Oct.2025.xlsx'
new_df.to_excel(output_file, index=False)
print(f"\nتم حفظ الملف: {output_file}")

# Show some examples
print("\nأمثلة على النتيجة:")
print(new_df[mobile_column].head(15))


