import json
import os
from collections import Counter

def extract_cf_closed_by_values(json_file_path):
    """
    يستخرج جميع القيم من حقل cf_closed_by من ملف JSON
    
    Args:
        json_file_path: مسار ملف JSON
        
    Returns:
        قائمة بالقيم الفريدة وعدد المرات التي ظهرت فيها كل قيمة
    """
    all_values = []
    
    try:
        # قراءة ملف JSON
        with open(json_file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        # التحقق من وجود collected_data
        if 'collected_data' in data and isinstance(data['collected_data'], list):
            # التنقل في جميع التذاكر
            for item in data['collected_data']:
                if 'ticket' in item and 'cf' in item['ticket']:
                    cf_closed_by = item['ticket']['cf'].get('cf_closed_by')
                    
                    # إضافة القيمة فقط إذا لم تكن None
                    if cf_closed_by is not None:
                        all_values.append(cf_closed_by)
        
        # حساب عدد المرات لكل قيمة
        value_counts = Counter(all_values)
        
        # ترتيب القيم حسب عدد المرات (من الأكثر إلى الأقل)
        sorted_counts = sorted(value_counts.items(), key=lambda x: x[1], reverse=True)
        
        return all_values, value_counts, sorted_counts
        
    except FileNotFoundError:
        print(f"❌ الملف غير موجود: {json_file_path}")
        return [], Counter(), []
    except json.JSONDecodeError as e:
        print(f"❌ خطأ في قراءة ملف JSON: {e}")
        return [], Counter(), []
    except Exception as e:
        print(f"❌ خطأ غير متوقع: {e}")
        return [], Counter(), []


def main():
    # مسار الملف
    json_file = "apiparsing/progress_500_tickets.json"
    
    print("=" * 60)
    print("🔥 جمع قيم cf_closed_by من ملف JSON")
    print("=" * 60)
    
    # استخراج القيم
    all_values, value_counts, sorted_counts = extract_cf_closed_by_values(json_file)
    
    # عرض النتائج
    print(f"\n✅ تم العثور على {len(all_values)} قيمة في حقل cf_closed_by")
    print(f"📊 عدد القيم الفريدة: {len(value_counts)}")
    
    # عرض جميع القيم المختلفة مع عدد مرات ظهورها
    if sorted_counts:
        print("\n" + "=" * 60)
        print("📋 القيم مرتبة حسب عدد مرات الظهور:")
        print("=" * 60)
        for value, count in sorted_counts:
            print(f"  • {value}: {count} مرة")
    
    # حفظ النتائج في ملف
    output_file = "cf_closed_by_values.json"
    output_data = {
        "total_values": len(all_values),
        "unique_values_count": len(value_counts),
        "unique_values": list(value_counts.keys()),
        "value_counts": dict(value_counts),
        "sorted_by_count": dict(sorted_counts)
    }
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output_data, f, ensure_ascii=False, indent=2)
    
    print(f"\n💾 تم حفظ النتائج في ملف: {output_file}")
    print("\n" + "=" * 60)


if __name__ == "__main__":
    main()

