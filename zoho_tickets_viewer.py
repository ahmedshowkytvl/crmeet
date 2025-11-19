import tkinter as tk
from tkinter import ttk, messagebox, simpledialog, scrolledtext
import psycopg2
import json
import requests
import os
from datetime import datetime, timedelta

# إعداد نسخ النص للـ clipboard
try:
    import tkinter.clipboard as clipboard
except ImportError:
    clipboard = None

# إعداد نظام Log
class Logger:
    def __init__(self, log_file="zoho_viewer.log"):
        self.log_file = log_file
    
    def log(self, message, level="INFO"):
        """تسجيل رسالة في ملف Log"""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        log_entry = f"[{timestamp}] [{level}] {message}\n"
        
        try:
            with open(self.log_file, "a", encoding="utf-8") as f:
                f.write(log_entry)
        except:
            print(f"[{level}] {message}")
    
    def error(self, message):
        """تسجيل خطأ"""
        self.log(message, "ERROR")
    
    def success(self, message):
        """تسجيل نجاح"""
        self.log(message, "SUCCESS")
    
    def warning(self, message):
        """تسجيل تحذير"""
        self.log(message, "WARNING")

# إنشاء instance للـ Logger
logger = Logger()

class ZohoTicketsViewer:
    def __init__(self, root):
        self.root = root
        self.root.title("مشاهدة تذاكر Zoho")
        self.root.geometry("1400x750")
        
        # Logger
        self.logger = logger
        
        # إعدادات قاعدة البيانات
        self.db_config = {
            'host': '127.0.0.1',
            'port': 5432,
            'database': 'CRM_ALL',
            'user': 'postgres',
            'password': ''
        }
        
        # إعدادات Zoho API
        self.zoho_config = {
            'client_id': '1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT',
            'client_secret': '30624b06180b20ab5252fc8e6145ad175762a367a0',
            'refresh_token': '1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee',
            'org_id': '786481962',
            'token_url': 'https://accounts.zoho.com/oauth/v2/token',
            'base_url': 'https://desk.zoho.com/api/v1'
        }
        self.zoho_access_token = None
        
        # إنشاء واجهة المستخدم
        self.create_widgets()
        
        # الاتصال بقاعدة البيانات وعرض البيانات
        self.load_data()
    
    def create_widgets(self):
        # إنشاء frame للعناصر العلوية
        top_frame = tk.Frame(self.root, bg="#2c3e50", pady=10)
        top_frame.pack(fill=tk.X)
        
        # عنوان
        title_label = tk.Label(
            top_frame, 
            text="تذاكر Zoho",
            font=("Arial", 14, "bold"),
            bg="#2c3e50",
            fg="white"
        )
        title_label.pack(side=tk.LEFT, padx=10)
        
        # حقل البحث
        search_frame = tk.Frame(top_frame, bg="#2c3e50")
        search_frame.pack(side=tk.LEFT, padx=10)
        
        search_label = tk.Label(
            search_frame,
            text="🔍 بحث:",
            font=("Arial", 11),
            bg="#2c3e50",
            fg="white"
        )
        search_label.pack(side=tk.LEFT, padx=5)
        
        self.search_var = tk.StringVar()
        self.search_var.trace('w', lambda *args: self.filter_data())
        
        search_entry = tk.Entry(
            search_frame,
            textvariable=self.search_var,
            font=("Arial", 11),
            width=25
        )
        search_entry.pack(side=tk.LEFT)
        
        # فلتر حسب Closed By
        filter_label = tk.Label(
            search_frame,
            text="📍 Closed By:",
            font=("Arial", 11),
            bg="#2c3e50",
            fg="white"
        )
        filter_label.pack(side=tk.LEFT, padx=(15, 5))
        
        self.closed_by_filter = tk.StringVar()
        self.closed_by_filter.set("الكل")  # القيمة الافتراضية
        self.closed_by_filter.trace('w', lambda *args: self.apply_closed_by_filter())
        
        closed_by_combo = ttk.Combobox(
            search_frame,
            textvariable=self.closed_by_filter,
            font=("Arial", 11),
            width=20,
            state="readonly"
        )
        closed_by_combo.pack(side=tk.LEFT)
        self.closed_by_combo = closed_by_combo
        
        # أزرار التحكم
        btn_frame = tk.Frame(top_frame, bg="#2c3e50")
        btn_frame.pack(side=tk.RIGHT, padx=10)
        
        # زر تحديث شامل: من Zoho + Get Closed By (الأهم - نضعه أولاً)
        update_and_extract_btn = tk.Button(
            btn_frame,
            text="🔄 Update & Extract",
            command=self.update_from_zoho_and_extract_closed_by,
            bg="#8e44ad",
            fg="white",
            font=("Arial", 10, "bold"),
            width=18
        )
        update_and_extract_btn.pack(side=tk.RIGHT, padx=3)
        
        stats_btn = tk.Button(
            btn_frame,
            text="📈 إحصائيات",
            command=self.show_stats,
            bg="#9b59b6",
            fg="white",
            font=("Arial", 10, "bold"),
            width=12
        )
        stats_btn.pack(side=tk.RIGHT, padx=3)
        
        refresh_btn = tk.Button(
            btn_frame,
            text="🔄 تحديث",
            command=self.load_data,
            bg="#3498db",
            fg="white",
            font=("Arial", 10, "bold"),
            width=10
        )
        refresh_btn.pack(side=tk.RIGHT, padx=3)
        
        # زر جلب تذاكر جديدة من Zoho
        fetch_new_btn = tk.Button(
            btn_frame,
            text="➕ تذاكر جديدة",
            command=self.fetch_new_tickets_from_zoho,
            bg="#27ae60",
            fg="white",
            font=("Arial", 10, "bold"),
            width=13
        )
        fetch_new_btn.pack(side=tk.RIGHT, padx=3)
        
        # زر جلب تذاكر من آخر وقت حتى الآن
        fetch_incremental_btn = tk.Button(
            btn_frame,
            text="🔄 تحديثات",
            command=self.fetch_incremental_tickets,
            bg="#f39c12",
            fg="white",
            font=("Arial", 10, "bold"),
            width=12
        )
        fetch_incremental_btn.pack(side=tk.RIGHT, padx=3)
        
        # زر قراءة من clipboard
        paste_btn = tk.Button(
            btn_frame,
            text="📋 Clipboard",
            command=self.paste_from_clipboard,
            bg="#e74c3c",
            fg="white",
            font=("Arial", 10, "bold"),
            width=12
        )
        paste_btn.pack(side=tk.RIGHT, padx=3)
        
        # إطار إحصائيات واضح
        stats_frame = tk.Frame(top_frame, bg="#34495e", relief=tk.RAISED, bd=2)
        stats_frame.pack(side=tk.RIGHT, padx=20)
        
        # عدد الصفوف - أكبر وأوضح
        count_label = tk.Label(
            stats_frame,
            text="📊 عدد الصفوف: 0",
            font=("Arial", 14, "bold"),
            bg="#34495e",
            fg="#2ecc71"
        )
        count_label.pack(side=tk.RIGHT, padx=15, pady=5)
        self.count_label = count_label
        
        # حالة التحميل
        status_label = tk.Label(
            stats_frame,
            text="🔄 جاري التحميل...",
            font=("Arial", 10),
            bg="#34495e",
            fg="#ecf0f1"
        )
        status_label.pack(side=tk.RIGHT, padx=15)
        self.status_label = status_label
        
        # إنشاء treeview مع scrollbars
        main_frame = tk.Frame(self.root)
        main_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # إنشاء scrollbars
        scrollbar_y = ttk.Scrollbar(main_frame, orient=tk.VERTICAL)
        scrollbar_x = ttk.Scrollbar(main_frame, orient=tk.HORIZONTAL)
        
        # إنشاء treeview
        columns = (
            'ID', 'Zoho Ticket ID', 'Ticket Number', 'User ID', 'Closed By',
            'Subject', 'Status', 'Department ID', 'Created At Zoho', 
            'Closed At Zoho', 'Response Time', 'Thread Count', 'Created At', 'Raw Data'
        )
        
        self.tree = ttk.Treeview(
            main_frame,
            columns=columns,
            show='tree headings',
            yscrollcommand=scrollbar_y.set,
            xscrollcommand=scrollbar_x.set,
            height=20
        )
        
        # ربط scrollbars
        scrollbar_y.config(command=self.tree.yview)
        scrollbar_x.config(command=self.tree.xview)
        
        # تعريف العناوين والأعمدة
        self.tree.heading('#0', text='')
        self.tree.column('#0', width=0, stretch=tk.NO)
        
        # إعداد الأعمدة
        column_widths = {
            'ID': 50,
            'Zoho Ticket ID': 120,
            'Ticket Number': 100,
            'User ID': 80,
            'Closed By': 100,
            'Subject': 200,
            'Status': 80,
            'Department ID': 100,
            'Created At Zoho': 150,
            'Closed At Zoho': 150,
            'Response Time': 100,
            'Thread Count': 80,
            'Created At': 150,
            'Raw Data': 300
        }
        
        for col in columns:
            self.tree.heading(col, text=col)
            self.tree.column(col, width=column_widths.get(col, 100), anchor=tk.W)
        
        # ربط أحداث النقر
        self.tree.bind('<Double-Button-1>', self.on_item_double_click)
        self.tree.bind('<Button-3>', self.show_context_menu)  # Right-click
        self.tree.bind('<Control-c>', self.copy_selected)  # Ctrl+C
        self.tree.bind('<Control-C>', self.copy_selected)  # Ctrl+C (upper)
        
        # ترتيب العناصر
        self.tree.grid(row=0, column=0, sticky='nsew')
        scrollbar_y.grid(row=0, column=1, sticky='ns')
        scrollbar_x.grid(row=1, column=0, sticky='ew')
        
        main_frame.grid_rowconfigure(0, weight=1)
        main_frame.grid_columnconfigure(0, weight=1)
        
        # حفظ البيانات الكاملة للوصول إليها
        self.full_data = {}
        
        # حفظ جميع الصفوف لاستخدامها في البحث
        self.all_items = []
        
        # إنشاء قائمة منبثقة للنسخ
        self.context_menu = tk.Menu(self.root, tearoff=0)
        self.context_menu.add_command(label="📋 نسخ الصف (Copy Row)", command=self.copy_selected_row)
        self.context_menu.add_command(label="📄 نسخ جميع البيانات (Copy All Data)", command=self.copy_all_data)
        self.context_menu.add_separator()
        self.context_menu.add_command(label="💬 عرض المحادثات", command=self.show_ticket_threads)
        self.context_menu.add_separator()
        self.context_menu.add_command(label="🔄 تحديث من Zoho", command=self.update_selected_from_zoho)
        self.context_menu.add_separator()
        self.context_menu.add_command(label="✅ تغيير إلى Open", command=self.change_ticket_to_open)
        self.context_menu.add_command(label="🔒 تغيير إلى Closed", command=self.change_ticket_to_closed)
    
    def connect_db(self):
        """الاتصال بقاعدة البيانات"""
        try:
            conn = psycopg2.connect(**self.db_config)
            return conn
        except psycopg2.Error as e:
            messagebox.showerror("خطأ في الاتصال", f"لا يمكن الاتصال بقاعدة البيانات:\n{str(e)}")
            return None
    
    def load_data(self):
        """تحميل البيانات من قاعدة البيانات"""
        # عرض حالة التحميل
        self.status_label.config(text="🔄 جاري التحميل...", fg="#f39c12")
        self.root.update()
        
        # مسح البيانات القديمة
        for item in self.tree.get_children():
            self.tree.delete(item)
        
        # مسح البيانات المحفوظة
        self.all_items = []
        self.full_data = {}
        
        conn = self.connect_db()
        if not conn:
            self.status_label.config(text="❌ فشل الاتصال", fg="#e74c3c")
            return
        
        try:
            cursor = conn.cursor()
            
            # استعلام SQL للحصول على قيم Closed By الفريدة
            closed_by_query = """
                SELECT DISTINCT closed_by_name 
                FROM zoho_tickets_cache 
                WHERE closed_by_name IS NOT NULL AND closed_by_name != ''
                ORDER BY closed_by_name
            """
            cursor.execute(closed_by_query)
            closed_by_values = [row[0] for row in cursor.fetchall()]
            
            # تحديث Combobox مع القيم - إضافة خيار "فارغ"
            self.closed_by_combo['values'] = ['الكل', 'فارغ (Blank)'] + closed_by_values
            
            # استعلام SQL للحصول على عدد الصفوف الفعلي
            count_query = "SELECT COUNT(*) FROM zoho_tickets_cache"
            cursor.execute(count_query)
            total_count = cursor.fetchone()[0]
            
            # استعلام SQL لاستعادة البيانات
            query = """
                SELECT 
                    id, zoho_ticket_id, ticket_number, user_id, closed_by_name,
                    subject, status, department_id, created_at_zoho, 
                    closed_at_zoho, response_time_minutes, thread_count, created_at,
                    raw_data
                FROM zoho_tickets_cache
                ORDER BY created_at DESC
            """
            
            cursor.execute(query)
            rows = cursor.fetchall()
            
            # إدراج البيانات في الجدول
            inserted_count = 0
            for row in rows:
                # تحويل التواريخ والبيانات
                formatted_row = []
                for i, value in enumerate(row):
                    if isinstance(value, datetime):
                        formatted_row.append(value.strftime('%Y-%m-%d %H:%M'))
                    elif value is None:
                        formatted_row.append('')
                    elif isinstance(value, (dict, list)):
                        # معالجة JSON data - تقصير إذا كان طويلاً
                        try:
                            json_str = json.dumps(value, ensure_ascii=False, indent=2)
                            if len(json_str) > 200:
                                formatted_row.append(json_str[:200] + '...')
                            else:
                                formatted_row.append(json_str)
                        except:
                            formatted_row.append(str(value)[:200])
                    else:
                        # تحويل إلى سلسلة مع معالجة النصوص الطويلة
                        str_value = str(value)
                        if len(str_value) > 300:
                            formatted_row.append(str_value[:300] + '...')
                        else:
                            formatted_row.append(str_value)
                
                # إدراج الصف مع حفظ ID للوصول للبيانات الكاملة
                inserted_item = self.tree.insert('', tk.END, values=tuple(formatted_row))
                # حفظ البيانات الكاملة في dictionary
                self.full_data[inserted_item] = row
                # حفظ في القائمة لاستخدامها في البحث
                self.all_items.append({
                    'item': inserted_item,
                    'row': row,
                    'formatted': formatted_row
                })
                inserted_count += 1
                
                # تحديث الواجهة كل 100 صف
                if inserted_count % 100 == 0:
                    self.root.update()
                    self.status_label.config(text=f"🔄 جاري التحميل... ({inserted_count}/{total_count})")
            
            # تحديث عدد السجلات - عرض واضح مع فواصل الأرقام
            count_text = f"📊 عدد الصفوف: {inserted_count:,} صف"
            self.count_label.config(text=count_text)
            
            # حالة نجاح
            success_text = f"✅ تم التحميل: {inserted_count:,} صف"
            self.status_label.config(text=success_text, fg="#27ae60")
            
            # عرض رسالة نجاح في الكونسول
            if inserted_count > 0:
                try:
                    print(f"Successfully loaded {inserted_count:,} rows from database")
                    print(f"Total rows in table: {inserted_count:,} rows")
                except:
                    print(f"Loaded {inserted_count:,} rows")
            
            cursor.close()
            conn.close()
            
        except psycopg2.Error as e:
            self.status_label.config(text="❌ خطأ في البيانات", fg="#e74c3c")
            messagebox.showerror("خطأ في البيانات", f"حدث خطأ عند تحميل البيانات:\n{str(e)}")
            if conn:
                conn.close()
    
    def show_stats(self):
        """عرض إحصائيات تفصيلية"""
        conn = self.connect_db()
        if not conn:
            return
        
        try:
            cursor = conn.cursor()
            
            # إحصائيات مختلفة
            stats = {}
            
            # عدد الصفوف الإجمالي
            cursor.execute("SELECT COUNT(*) FROM zoho_tickets_cache")
            stats['total'] = cursor.fetchone()[0]
            
            # عدد التذاكر المغلقة
            cursor.execute("SELECT COUNT(*) FROM zoho_tickets_cache WHERE status = 'Closed'")
            stats['closed'] = cursor.fetchone()[0]
            
            # عدد التذاكر المفتوحة
            cursor.execute("SELECT COUNT(*) FROM zoho_tickets_cache WHERE status = 'Open'")
            stats['open'] = cursor.fetchone()[0]
            
            # عدد التذاكر قيد العمل
            cursor.execute("SELECT COUNT(*) FROM zoho_tickets_cache WHERE status = 'In Progress'")
            stats['in_progress'] = cursor.fetchone()[0]
            
            # عدد التذاكر المغلقة بغير Auto Close
            cursor.execute("SELECT COUNT(*) FROM zoho_tickets_cache WHERE closed_by_name != 'Auto Close' OR closed_by_name IS NULL")
            stats['not_auto'] = cursor.fetchone()[0]
            
            cursor.close()
            conn.close()
            
            # عرض النتائج
            stats_text = f"""
📊 إحصائيات تذاكر Zoho
━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ إجمالي التذاكر: {stats['total']:,}
🔴 التذاكر المغلقة: {stats['closed']:,}
🟢 التذاكر المفتوحة: {stats['open']:,}
🟡 قيد العمل: {stats['in_progress']:,}
🚫 المغلقة يدوياً: {stats['not_auto']:,}
━━━━━━━━━━━━━━━━━━━━━━━━━━━
"""
            
            messagebox.showinfo("إحصائيات", stats_text)
            
        except psycopg2.Error as e:
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
            if conn:
                conn.close()
    
    def filter_data(self):
        """تصفية البيانات بناءً على نص البحث وClosed By"""
        search_text = self.search_var.get().lower()
        closed_by_filter = self.closed_by_filter.get()
        
        # إعادة جميع الصفوف المحذوفة أولاً
        for item_info in self.all_items:
            item = item_info['item']
            parent = self.tree.parent(item)
            # إذا كان الصف مخفي (detached)
            if parent == '':  # detached items have empty parent
                try:
                    # إعادة الصف مرة أخرى
                    self.tree.reattach(item, '', 'end')
                except:
                    pass
        
        # تطبيق الفلاتر - إخفاء الصفوف التي لا تطابق
        for item_info in self.all_items:
            should_show = True
            item = item_info['item']
            
            # فلترة حسب البحث
            if search_text:
                found = False
                for value in item_info['formatted']:
                    if search_text in str(value).lower():
                        found = True
                        break
                should_show = should_show and found
            
            # فلترة حسب Closed By
            if closed_by_filter and closed_by_filter != 'الكل':
                row_data = item_info['row']
                closed_by_value = row_data[4] if len(row_data) > 4 else ''  # closed_by_name at index 4
                
                # التحقق من خيار "فارغ"
                if closed_by_filter == 'فارغ (Blank)':
                    # إظهار فقط التذاكر التي closed_by_name فارغ أو None أو فارغ string
                    should_show = should_show and (not closed_by_value or closed_by_value.strip() == '')
                else:
                    # فلترة عادية بالمطابقة
                    should_show = should_show and (closed_by_value == closed_by_filter)
            
            # إخفاء الصف إذا لم يطابق الفلاتر
            if not should_show:
                try:
                    self.tree.detach(item)
                except:
                    pass
    
    def apply_closed_by_filter(self):
        """تطبيق فلتر Closed By"""
        self.filter_data()
    
    def update_selected_from_zoho(self):
        """تحديث الصفوف المحددة من Zoho API"""
        selected = self.tree.selection()
        
        if not selected:
            messagebox.showwarning("تحذير", "يرجى تحديد صف أو أكثر لتحديثها")
            return
        
        # جمع جميع التذاكر المحددة
        tickets_to_update = []
        for item in selected:
            if item not in self.full_data:
                continue
            
            row_data = self.full_data[item]
            ticket_id = row_data[1] if len(row_data) > 1 else None  # zoho_ticket_id
            
            if ticket_id:
                tickets_to_update.append({
                    'item': item,
                    'ticket_id': ticket_id,
                    'ticket_number': row_data[2] if len(row_data) > 2 else 'N/A'
                })
        
        if not tickets_to_update:
            messagebox.showerror("خطأ", "لا توجد تذاكر صالحة للتحديث")
            return
        
        # تأكيد من المستخدم
        ticket_count = len(tickets_to_update)
        if not messagebox.askyesno(
            "تأكيد", 
            f"هل تريد تحديث {ticket_count} تذكرة من Zoho؟\n\nهذه العملية قد تستغرق بضع دقائق."
        ):
            return
        
        # إنشاء نافذة progress
        progress_window = tk.Toplevel(self.root)
        progress_window.title("جاري التحديث...")
        progress_window.geometry("500x150")
        progress_window.transient(self.root)
        progress_window.grab_set()
        
        # Progress label
        progress_label = tk.Label(
            progress_window,
            text=f"جاري تحديث التذاكر... (0/{ticket_count})",
            font=("Arial", 12),
            pady=20
        )
        progress_label.pack()
        
        # Progress bar
        progress_bar = ttk.Progressbar(
            progress_window,
            length=400,
            mode='determinate',
            maximum=ticket_count
        )
        progress_bar.pack(pady=10)
        
        # Status label
        status_label = tk.Label(
            progress_window,
            text="",
            font=("Arial", 10),
            fg="blue"
        )
        status_label.pack()
        
        progress_window.update()
        
        # تحديث التذاكر
        updated_count = 0
        failed_count = 0
        failed_tickets = []
        
        try:
            for i, ticket_info in enumerate(tickets_to_update, 1):
                # تحديث النافذة
                progress_label.config(text=f"جاري تحديث التذاكر... ({i}/{ticket_count})")
                status_label.config(text=f"تحديث: {ticket_info['ticket_number']}")
                progress_bar['value'] = i
                progress_window.update()
                
                try:
                    # استرجاع البيانات الكاملة من Zoho
                    full_details = self.fetch_ticket_details_from_zoho(ticket_info['ticket_id'])
                    
                    if full_details:
                        # تحديث قاعدة البيانات
                        self.update_database_with_full_details(
                            ticket_info['ticket_id'], 
                            full_details
                        )
                        updated_count += 1
                    else:
                        failed_count += 1
                        failed_tickets.append(ticket_info['ticket_number'])
                except Exception as e:
                    failed_count += 1
                    failed_tickets.append(ticket_info['ticket_number'])
                    print(f"Error updating ticket {ticket_info['ticket_number']}: {e}")
            
            # إغلاق نافذة Progress
            progress_window.destroy()
            
            # عرض النتائج
            if failed_count == 0:
                messagebox.showinfo(
                    "نجاح", 
                    f"تم تحديث {updated_count} تذكرة بنجاح!"
                )
            else:
                messagebox.showwarning(
                    "تم الانتهاء", 
                    f"تم تحديث {updated_count} تذكرة.\nفشل {failed_count} تذكرة.\n\nالتذاكر الفاشلة:\n" + 
                    "\n".join(failed_tickets[:10])  # عرض أول 10
                )
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            progress_window.destroy()
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
    
    def update_database_with_full_details(self, ticket_id, full_details):
        """تحديث قاعدة البيانات بالتفاصيل الكاملة من Zoho"""
        conn = self.connect_db()
        if not conn:
            raise Exception("لا يمكن الاتصال بقاعدة البيانات")
        
        try:
            cursor = conn.cursor()
            
            # تحويل البيانات الكاملة إلى JSON
            full_data_json = json.dumps(full_details)
            
            # استخراج cf_closed_by من full_details
            cf_closed_by = None
            if 'cf' in full_details and isinstance(full_details['cf'], dict):
                cf_closed_by = full_details['cf'].get('cf_closed_by')
            
            # إذا لم توجد في cf، ابحث في customFields
            if not cf_closed_by or cf_closed_by == '':
                if 'customFields' in full_details and isinstance(full_details['customFields'], dict):
                    cf_closed_by = full_details['customFields'].get('Closed By')
            
            # تحديث raw_data و closed_by_name في قاعدة البيانات
            if cf_closed_by and cf_closed_by != '' and cf_closed_by != 'Auto Close' and cf_closed_by != 'Unknown Agent':
                update_query = """
                    UPDATE zoho_tickets_cache 
                    SET raw_data = %s,
                        closed_by_name = %s,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE zoho_ticket_id = %s
                """
                cursor.execute(update_query, (full_data_json, cf_closed_by, ticket_id))
                self.logger.log(f"Updated closed_by_name for ticket {ticket_id}: {cf_closed_by}")
            else:
                update_query = """
                    UPDATE zoho_tickets_cache 
                    SET raw_data = %s,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE zoho_ticket_id = %s
                """
                cursor.execute(update_query, (full_data_json, ticket_id))
            
            conn.commit()
            
            cursor.close()
            conn.close()
        except psycopg2.Error as e:
            if conn:
                conn.close()
            raise Exception(f"خطأ في قاعدة البيانات: {str(e)}")
    
    def get_zoho_access_token(self):
        """الحصول على Access Token من Zoho"""
        try:
            response = requests.post(self.zoho_config['token_url'], data={
                'refresh_token': self.zoho_config['refresh_token'],
                'client_id': self.zoho_config['client_id'],
                'client_secret': self.zoho_config['client_secret'],
                'grant_type': 'refresh_token'
            })
            
            if response.status_code == 200:
                data = response.json()
                self.zoho_access_token = data.get('access_token')
                return self.zoho_access_token
            else:
                return None
        except Exception as e:
            print(f"Error getting Zoho token: {e}")
            return None
    
    def fetch_ticket_details_from_zoho(self, ticket_id):
        """استرجاع تفاصيل التذكرة الكاملة من Zoho API"""
        try:
            # الحصول على access token
            token = self.get_zoho_access_token()
            if not token:
                return None
            
            # استدعاء API للحصول على التفاصيل الكاملة
            url = f"{self.zoho_config['base_url']}/tickets/{ticket_id}"
            headers = {
                'Authorization': f'Zoho-oauthtoken {token}',
                'Content-Type': 'application/json'
            }
            params = {
                'orgId': self.zoho_config['org_id']
            }
            
            response = requests.get(url, headers=headers, params=params)
            
            if response.status_code == 200:
                return response.json()
            else:
                return None
        except Exception as e:
            print(f"Error fetching ticket details: {e}")
            return None
    
    def update_ticket_from_db(self, ticket_id, tree_item):
        """تحديث التذكرة من قاعدة البيانات باستخدام zoho_ticket_id"""
        conn = self.connect_db()
        if not conn:
            raise Exception("لا يمكن الاتصال بقاعدة البيانات")
        
        try:
            cursor = conn.cursor()
            
            # استرجاع التذكرة المحدثة
            query = """
                SELECT 
                    id, zoho_ticket_id, ticket_number, user_id, closed_by_name,
                    subject, status, department_id, created_at_zoho, 
                    closed_at_zoho, response_time_minutes, thread_count, created_at,
                    raw_data
                FROM zoho_tickets_cache
                WHERE zoho_ticket_id = %s
                LIMIT 1
            """
            
            cursor.execute(query, (ticket_id,))
            updated_row = cursor.fetchone()
            
            if updated_row:
                # تحديث البيانات المحفوظة
                self.full_data[tree_item] = updated_row
                
                # تحديث العرض في الشجرة
                formatted_row = []
                for i, value in enumerate(updated_row):
                    if isinstance(value, datetime):
                        formatted_row.append(value.strftime('%Y-%m-%d %H:%M'))
                    elif value is None:
                        formatted_row.append('')
                    elif isinstance(value, (dict, list)):
                        try:
                            json_str = json.dumps(value, ensure_ascii=False, indent=2)
                            if len(json_str) > 200:
                                formatted_row.append(json_str[:200] + '...')
                            else:
                                formatted_row.append(json_str)
                        except:
                            formatted_row.append(str(value)[:200])
                    else:
                        str_value = str(value)
                        if len(str_value) > 300:
                            formatted_row.append(str_value[:300] + '...')
                        else:
                            formatted_row.append(str_value)
                
                # تحديث القيم في الشجرة
                self.tree.item(tree_item, values=tuple(formatted_row))
                
                # تحديث في self.all_items
                for item_info in self.all_items:
                    if item_info['item'] == tree_item:
                        item_info['row'] = updated_row
                        item_info['formatted'] = formatted_row
                        break
            
            cursor.close()
            conn.close()
            
        except psycopg2.Error as e:
            if conn:
                conn.close()
            raise Exception(f"خطأ في قاعدة البيانات: {str(e)}")

    def on_item_double_click(self, event):
        """معالجة النقر المزدوج لعرض البيانات الكاملة"""
        selected = self.tree.selection()
        if not selected:
            return
        
        item = selected[0]
        
        # الحصول على قيم الصف المحدد
        item_values = self.tree.item(item, 'values')
        
        if not item_values:
            return
        
        # عرض النافذة المنبثقة
        self.show_full_data(item_values, item)
    
    def show_full_data(self, values, item_id):
        """عرض بيانات كاملة في نافذة منبثقة"""
        # إنشاء نافذة جديدة
        popup = tk.Toplevel(self.root)
        popup.title("عرض البيانات الكاملة - Raw Data")
        popup.geometry("1000x600")
        popup.configure(bg="#ecf0f1")
        
        # عنوان
        title_frame = tk.Frame(popup, bg="#34495e", pady=10)
        title_frame.pack(fill=tk.X)
        
        title_label = tk.Label(
            title_frame,
            text="📋 البيانات الكاملة للصف المحدد",
            font=("Arial", 14, "bold"),
            bg="#34495e",
            fg="white"
        )
        title_label.pack(pady=5)
        
        # إنشاء منطقة النص مع scrollbar
        text_frame = tk.Frame(popup)
        text_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        text_widget = tk.Text(text_frame, wrap=tk.WORD, font=("Courier", 10))
        scrollbar = tk.Scrollbar(text_frame, command=text_widget.yview)
        text_widget.config(yscrollcommand=scrollbar.set)
        
        # إضافة البيانات
        data_text = "الأعمدة والبيانات:\n" + "="*80 + "\n\n"
        
        columns = (
            'ID', 'Zoho Ticket ID', 'Ticket Number', 'User ID', 'Closed By',
            'Subject', 'Status', 'Department ID', 'Created At Zoho', 
            'Closed At Zoho', 'Response Time', 'Thread Count', 'Created At', 'Raw Data'
        )
        
        # الحصول على البيانات الكاملة إذا كانت محفوظة
        if item_id in self.full_data:
            row_data = self.full_data[item_id]
            for i, col in enumerate(columns):
                data_text += f"{col}:\n"
                if i < len(row_data):
                    value = row_data[i]
                    if isinstance(value, (dict, list)):
                        data_text += json.dumps(value, ensure_ascii=False, indent=2)
                    elif isinstance(value, datetime):
                        data_text += value.strftime('%Y-%m-%d %H:%M:%S')
                    else:
                        data_text += str(value) if value is not None else 'N/A'
                else:
                    data_text += "N/A"
                data_text += "\n" + "-"*80 + "\n\n"
        else:
            # إذا لم توجد بيانات كاملة، استخدم القيم المعروضة
            for i, col in enumerate(columns):
                data_text += f"{col}:\n"
                data_text += f"{values[i] if i < len(values) else 'N/A'}\n"
                data_text += "-"*80 + "\n\n"
        
        text_widget.insert(tk.END, data_text)
        text_widget.config(state=tk.DISABLED)
        
        text_widget.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        # زر الإغلاق
        close_btn = tk.Button(
            popup,
            text="إغلاق",
            command=popup.destroy,
            bg="#e74c3c",
            fg="white",
            font=("Arial", 12, "bold"),
            width=20,
            pady=5
        )
        close_btn.pack(pady=10)
    
    def extract_closed_by_from_raw_data(self):
        """استخراج قيم Closed By من raw_data وتحديث عمود Closed By"""
        # تأكيد من المستخدم
        if not messagebox.askyesno(
            "تأكيد",
            "هل تريد استخراج قيم 'cf_closed_by' من raw_data وتحديث عمود 'Closed By' لجميع التذاكر؟"
        ):
            return
        
        # إنشاء نافذة progress
        progress_window = tk.Toplevel(self.root)
        progress_window.title("جاري الاستخراج...")
        progress_window.geometry("500x150")
        progress_window.transient(self.root)
        progress_window.grab_set()
        
        progress_label = tk.Label(
            progress_window,
            text="جاري استخراج البيانات...",
            font=("Arial", 12),
            pady=20
        )
        progress_label.pack()
        
        progress_bar = ttk.Progressbar(
            progress_window,
            length=400,
            mode='determinate',
            maximum=len(self.all_items)
        )
        progress_bar.pack(pady=10)
        
        progress_window.update()
        
        updated_count = 0
        not_found_count = 0
        
        try:
            conn = self.connect_db()
            if not conn:
                raise Exception("لا يمكن الاتصال بقاعدة البيانات")
            
            cursor = conn.cursor()
            
            for i, item_info in enumerate(self.all_items, 1):
                # تحديث النافذة
                progress_label.config(text=f"جاري الاستخراج... ({i}/{len(self.all_items)})")
                progress_bar['value'] = i
                progress_window.update()
                
                try:
                    row_data = item_info['row']
                    zoho_ticket_id = row_data[1] if len(row_data) > 1 else None
                    raw_data = row_data[13] if len(row_data) > 13 else None  # raw_data at index 13
                    
                    if not raw_data or not zoho_ticket_id:
                        not_found_count += 1
                        continue
                    
                    # تحويل raw_data إلى dict إذا كان JSON string
                    if isinstance(raw_data, str):
                        try:
                            raw_data_dict = json.loads(raw_data)
                        except:
                            not_found_count += 1
                            continue
                    else:
                        raw_data_dict = raw_data
                    
                    # البحث عن cf_closed_by في raw_data
                    closed_by_value = None
                    
                    # التحقق من وجود cf.customFields
                    if isinstance(raw_data_dict, dict):
                        # البحث في cf
                        if 'cf' in raw_data_dict and isinstance(raw_data_dict['cf'], dict):
                            closed_by_value = raw_data_dict['cf'].get('cf_closed_by')
                        
                        # إذا لم توجد في cf، ابحث في customFields
                        if not closed_by_value:
                            if 'customFields' in raw_data_dict and isinstance(raw_data_dict['customFields'], dict):
                                closed_by_value = raw_data_dict['customFields'].get('Closed By')
                        
                        # إذا لم توجد، ابحث في المستوى العلوي
                        if not closed_by_value:
                            closed_by_value = raw_data_dict.get('cf_closed_by')
                    
                    # تحديث قاعدة البيانات إذا وجدت قيمة
                    if closed_by_value:
                        update_query = """
                            UPDATE zoho_tickets_cache 
                            SET closed_by_name = %s,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE zoho_ticket_id = %s
                        """
                        cursor.execute(update_query, (closed_by_value, zoho_ticket_id))
                        updated_count += 1
                    else:
                        not_found_count += 1
                        
                except Exception as e:
                    print(f"Error processing row: {e}")
                    not_found_count += 1
            
            conn.commit()
            cursor.close()
            conn.close()
            
            progress_window.destroy()
            
            # عرض النتائج
            messagebox.showinfo(
                "تم الانتهاء",
                f"تم تحديث {updated_count} تذكرة.\nالتذاكر غير المحدثة: {not_found_count}"
            )
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            progress_window.destroy()
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
    
    def update_from_zoho_and_extract_closed_by(self):
        """تحديث من Zoho ثم استخراج Closed By - على التذاكر المحددة فقط"""
        # الحصول على التذاكر المحددة
        selected_items = self.tree.selection()
        
        if not selected_items:
            messagebox.showwarning("تحذير", "لم يتم تحديد أي تذاكر. يرجى تحديد تذكرة أو أكثر")
            return
        
        # جمع معلومات التذاكر المحددة
        tickets_to_update = []
        for item in selected_items:
            if item not in self.full_data:
                continue
            row_data = self.full_data[item]
            ticket_id = row_data[1] if len(row_data) > 1 else None
            
            if ticket_id:
                tickets_to_update.append({
                    'item': item,
                    'ticket_id': ticket_id,
                    'ticket_number': row_data[2] if len(row_data) > 2 else 'N/A'
                })
        
        if not tickets_to_update:
            messagebox.showerror("خطأ", "لا توجد تذاكر صالحة للتحديث")
            return
        
        # تأكيد من المستخدم
        if not messagebox.askyesno(
            "تأكيد",
            f"هل تريد تحديث {len(tickets_to_update)} تذاكر محددة من Zoho ثم استخراج قيم Closed By؟\n\nهذه العملية قد تستغرق بضع دقائق."
        ):
            return
        
        # نافذة progress
        progress_window = tk.Toplevel(self.root)
        progress_window.title("جاري التحديث...")
        progress_window.geometry("500x250")
        progress_window.transient(self.root)
        progress_window.grab_set()
        
        progress_label = tk.Label(
            progress_window,
            text="جاري التحديث والاستخراج...",
            font=("Arial", 12, "bold"),
            pady=20
        )
        progress_label.pack()
        
        progress_bar = ttk.Progressbar(
            progress_window,
            length=400,
            mode='determinate',
            maximum=len(tickets_to_update)
        )
        progress_bar.pack(pady=10)
        
        status_label = tk.Label(
            progress_window,
            text="",
            font=("Arial", 10),
            fg="blue"
        )
        status_label.pack()
        
        # زر إلغاء
        self.cancel_updating = False
        cancel_btn = tk.Button(
            progress_window,
            text="❌ إيقاف العملية",
            command=lambda: setattr(self, 'cancel_updating', True),
            bg="#e74c3c",
            fg="white",
            font=("Arial", 10, "bold"),
            width=20,
            pady=5
        )
        cancel_btn.pack(pady=10)
        
        progress_window.update()
        
        updated_count = 0
        extracted_count = 0
        failed_count = 0
        skipped_count = 0
        
        try:
            conn = self.connect_db()
            if not conn:
                raise Exception("لا يمكن الاتصال بقاعدة البيانات")
            
            cursor = conn.cursor()
            
            # Loop واحد لكل تذكرة محددة
            for i, ticket_info in enumerate(tickets_to_update, 1):
                # التحقق من طلب الإلغاء
                if self.cancel_updating:
                    self.logger.log("Update & Extract cancelled by user")
                    progress_window.destroy()
                    messagebox.showinfo("تم الإلغاء", f"تم إيقاف العملية\nتم تحديث {updated_count} تذكرة")
                    self.load_data()
                    return
                
                ticket_id = ticket_info['ticket_id']
                ticket_number = ticket_info['ticket_number']
                
                try:
                    # التحقق من وجود closed_by_name في قاعدة البيانات
                    check_query = "SELECT closed_by_name FROM zoho_tickets_cache WHERE zoho_ticket_id = %s"
                    cursor.execute(check_query, (ticket_id,))
                    result = cursor.fetchone()
                    
                    # إذا كان closed_by_name موجود وفيه قيمة، تخطي هذه التذكرة
                    if result and result[0] and result[0].strip() and result[0].strip() not in ['غير محدد', 'Unknown Agent', 'Auto Close', '']:
                        self.logger.log(f"Skipping ticket {ticket_number} - closed_by_name already has value: {result[0]}")
                        skipped_count += 1
                        # تحديث النافذة حتى لو تم التخطي
                        try:
                            progress_label.config(text=f"تخطي {i}/{len(tickets_to_update)}: {ticket_number}")
                            status_label.config(text=f"تم تخطيها - closed_by موجود")
                            progress_bar['value'] = i
                            progress_window.update()
                        except:
                            pass
                        continue  # اذهب للتذكرة التالية
                    
                    # تحديث النافذة (فقط إذا كانت نافذة موجودة)
                    try:
                        progress_label.config(text=f"التذكرة {i}/{len(tickets_to_update)}")
                        status_label.config(text=f"تحديث: {ticket_number}")
                        progress_bar['value'] = i
                        progress_window.update()
                    except:
                        pass  # تجاهل الأخطاء في تحديث النافذة
                    
                    # STEP 1: تحديث raw_data من Zoho
                    full_details = self.fetch_ticket_details_from_zoho(ticket_id)
                    
                    if full_details:
                        # حفظ raw_data في قاعدة البيانات
                        full_data_json = json.dumps(full_details)
                        update_query = """
                            UPDATE zoho_tickets_cache 
                            SET raw_data = %s,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE zoho_ticket_id = %s
                        """
                        cursor.execute(update_query, (full_data_json, ticket_id))
                        updated_count += 1
                        
                        # STEP 2: استخراج Closed By من نفس raw_data الذي تم تحديثه
                        closed_by_value = None
                        
                        # البحث في cf
                        if 'cf' in full_details and isinstance(full_details['cf'], dict):
                            closed_by_value = full_details['cf'].get('cf_closed_by')
                        
                        # إذا لم توجد في cf، ابحث في customFields
                        if not closed_by_value:
                            if 'customFields' in full_details and isinstance(full_details['customFields'], dict):
                                closed_by_value = full_details['customFields'].get('Closed By')
                        
                        # إذا لم توجد، ابحث في المستوى العلوي
                        if not closed_by_value:
                            closed_by_value = full_details.get('cf_closed_by')
                        
                        # STEP 3: تحديث عمود Closed By
                        if closed_by_value:
                            update_closed_by_query = """
                                UPDATE zoho_tickets_cache 
                                SET closed_by_name = %s
                                WHERE zoho_ticket_id = %s
                            """
                            cursor.execute(update_closed_by_query, (closed_by_value, ticket_id))
                            extracted_count += 1
                    else:
                        failed_count += 1
                        
                except Exception as e:
                    self.logger.error(f"Failed to update ticket {ticket_id}: {str(e)}")
                    failed_count += 1
            
            # Commit جميع التغييرات
            conn.commit()
            cursor.close()
            conn.close()
            
            progress_window.destroy()
            
            # عرض النتائج
            result_msg = (
                f"✅ تم تحديث {updated_count} تذكرة من Zoho\n"
                f"📊 تم استخراج {extracted_count} قيمة للـ Closed By\n"
                f"⏭️  تم تخطي {skipped_count} تذكرة (كان لها closed_by_name مسبقاً)\n\n"
                f"❌ فشل {failed_count} تذكرة"
            )
            
            messagebox.showinfo("تم الانتهاء", result_msg)
            self.logger.success(f"Update & Extract completed: {updated_count} updated, {extracted_count} extracted, {skipped_count} skipped, {failed_count} failed")
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            progress_window.destroy()
            error_msg = f"حدث خطأ:\n{str(e)}"
            messagebox.showerror("خطأ", error_msg)
            self.logger.error(f"Update & Extract failed: {str(e)}")
    
    def fetch_new_tickets_from_zoho(self):
        """جلب تذاكر جديدة من Zoho حسب الفترة الزمنية"""
        # إنشاء نافذة اختيار التاريخ
        date_window = tk.Toplevel(self.root)
        date_window.title("جلب تذاكر جديدة من Zoho")
        date_window.geometry("400x300")
        date_window.transient(self.root)
        date_window.grab_set()
        
        tk.Label(
            date_window,
            text="اختر فترة زمنية لجلب التذاكر:",
            font=("Arial", 12, "bold"),
            pady=20
        ).pack()
        
        # اختيار عدد الأيام
        days_frame = tk.Frame(date_window)
        days_frame.pack(pady=20)
        
        tk.Label(days_frame, text="عدد الأيام:", font=("Arial", 11)).pack(side=tk.LEFT, padx=10)
        
        days_var = tk.StringVar(value="7")
        days_spinbox = tk.Spinbox(
            days_frame,
            from_=1,
            to=365,
            textvariable=days_var,
            width=10,
            font=("Arial", 11)
        )
        days_spinbox.pack(side=tk.LEFT, padx=10)
        
        tk.Label(days_frame, text="أيام", font=("Arial", 11)).pack(side=tk.LEFT, padx=5)
        
        # معلومات
        info_label = tk.Label(
            date_window,
            text="سيتم جلب التذاكر من آخر X يوم",
            font=("Arial", 10),
            fg="gray"
        )
        info_label.pack(pady=10)
        
        def update_info():
            days = days_var.get()
            info_label.config(text=f"سيتم جلب التذاكر من آخر {days} يوم")
        
        days_var.trace('w', lambda *args: update_info())
        
        # أزرار
        btn_frame = tk.Frame(date_window)
        btn_frame.pack(pady=30)
        
        def start_fetch():
            days = int(days_var.get())
            date_window.destroy()
            self.fetch_tickets_from_zoho(days)
        
        fetch_btn = tk.Button(
            btn_frame,
            text="جلب التذاكر",
            command=start_fetch,
            bg="#27ae60",
            fg="white",
            font=("Arial", 11, "bold"),
            width=15
        )
        fetch_btn.pack(side=tk.LEFT, padx=10)
        
        cancel_btn = tk.Button(
            btn_frame,
            text="إلغاء",
            command=date_window.destroy,
            bg="#e74c3c",
            fg="white",
            font=("Arial", 11, "bold"),
            width=15
        )
        cancel_btn.pack(side=tk.LEFT, padx=10)
    
    def fetch_tickets_from_zoho(self, days):
        """جلب تذاكر جديدة من Zoho"""
        # تأكيد
        if not messagebox.askyesno(
            "تأكيد",
            f"هل تريد جلب التذاكر من آخر {days} يوم من Zoho؟"
        ):
            return
        
        # نافذة progress
        progress_window = tk.Toplevel(self.root)
        progress_window.title("جاري جلب التذاكر...")
        progress_window.geometry("500x250")
        progress_window.transient(self.root)
        progress_window.grab_set()
        
        progress_label = tk.Label(
            progress_window,
            text="جاري جلب التذاكر...",
            font=("Arial", 12, "bold"),
            pady=20
        )
        progress_label.pack()
        
        progress_bar = ttk.Progressbar(
            progress_window,
            length=400,
            mode='indeterminate'
        )
        progress_bar.pack(pady=10)
        progress_bar.start()
        
        status_label = tk.Label(
            progress_window,
            text="",
            font=("Arial", 10),
            fg="blue"
        )
        status_label.pack()
        
        # زر إلغاء
        self.cancel_fetching = False
        cancel_btn = tk.Button(
            progress_window,
            text="❌ إيقاف العملية",
            command=lambda: setattr(self, 'cancel_fetching', True),
            bg="#e74c3c",
            fg="white",
            font=("Arial", 10, "bold"),
            width=20,
            pady=5
        )
        cancel_btn.pack(pady=10)
        
        progress_window.update()
        
        try:
            self.logger.log(f"Starting to fetch tickets from last {days} days from Zoho")
            
            # حساب التاريخ
            end_date = datetime.now()
            start_date = end_date - timedelta(days=days)
            
            # تنسيق التاريخ للـ API (ISO 8601)
            start_date_str = start_date.strftime('%Y-%m-%dT%H:%M:%S.000Z')
            end_date_str = end_date.strftime('%Y-%m-%dT%H:%M:%S.000Z')
            
            status_label.config(text="جاري الاتصال بـ Zoho...")
            progress_window.update()
            
            # الحصول على access token
            token = self.get_zoho_access_token()
            if not token:
                raise Exception("Failed to get Zoho access token")
            
            # استخدام /tickets/search للتذاكر المحدثة
            url = f"{self.zoho_config['base_url']}/tickets/search"
            headers = {
                'Authorization': f'Zoho-oauthtoken {token}',
                'Content-Type': 'application/json'
            }
            
            self.logger.log(f"Searching tickets from {start_date_str} to {end_date_str}")
            
            # Loop لجلب التذاكر في صفحات
            all_tickets = []
            page = 1
            total_fetched = 0
            
            while True:
                # التحقق من طلب الإلغاء
                if self.cancel_fetching:
                    self.logger.log("Fetching cancelled by user")
                    progress_window.destroy()
                    messagebox.showinfo("تم الإلغاء", "تم إيقاف العملية")
                    return
                
                status_label.config(text=f"جاري جلب التذاكر... صفحة {page}")
                progress_window.update()
                
                # إضافة offset لكل صفحة
                offset = (page - 1) * 100
                
                params = {
                    'orgId': self.zoho_config['org_id'],
                    'limit': 100,
                    'from': offset,
                    'sortBy': '-modifiedTime',
                    'modifiedTimeRange': f"{start_date_str},{end_date_str}"
                }
                
                self.logger.log(f"Fetching page {page} with offset {offset}")
                
                response = requests.get(url, headers=headers, params=params)
                
                if response.status_code != 200:
                    self.logger.error(f"API returned status {response.status_code}: {response.text[:200]}")
                    # إذا كان الخطأ 422 أو 400، قد تكون انتهت التذاكر
                    if response.status_code in [400, 422]:
                        self.logger.log("Reached end of tickets or invalid request")
                        break
                    raise Exception(f"API Error: {response.status_code} - {response.text[:200]}")
                
                data = response.json()
                tickets = data.get('data', [])
                
                if not tickets or len(tickets) == 0:
                    self.logger.log(f"No more tickets found on page {page}")
                    break
                
                self.logger.log(f"Page {page}: Got {len(tickets)} tickets")
                
                # إضافة التذاكر
                all_tickets.extend(tickets)
                total_fetched += len(tickets)
                
                # إذا كانت عدد التذاكر أقل من 100، انتهينا
                if len(tickets) < 100:
                    self.logger.log("Got less than 100 tickets, assuming end of data")
                    break
                
                page += 1
                
                # استراحة بعد كل 5 صفحات (5-7 دقائق)
                if page % 5 == 1 and page > 1:
                    wait_minutes = 6  # 6 دقائق
                    self.logger.log(f"Taking a {wait_minutes}-minute break after {(page-1)} pages...")
                    
                    status_label.config(text=f"استراحة... {wait_minutes} دقائق (تم جلب {total_fetched} تذكرة)")
                    progress_window.update()
                    
                    # انتظار مع تحديث النافذة كل دقيقة
                    import time
                    for minute in range(wait_minutes, 0, -1):
                        # التحقق من طلب الإلغاء
                        if self.cancel_fetching:
                            return
                        
                        try:
                            status_label.config(text=f"استراحة... {minute} دقيقة متبقية (تم جلب {total_fetched} تذكرة)")
                            progress_window.update()
                            time.sleep(60)  # انتظر دقيقة كاملة
                        except:
                            pass
                    
                    self.logger.log("Break completed, continuing...")
                
                # حد أقصى 50 صفحة (5000 تذكرة)
                if page > 50:
                    self.logger.warning("Reached max pages limit (50 pages)")
                    break
            
            self.logger.log(f"Total fetched: {total_fetched} tickets from {page-1} pages")
            
            status_label.config(text=f"تم جلب {len(all_tickets)} تذكرة، جاري الحفظ...")
            progress_window.update()
            
            # حفظ في قاعدة البيانات
            conn = self.connect_db()
            if not conn:
                raise Exception("Cannot connect to database")
            
            cursor = conn.cursor()
            saved_count = 0
            skipped_count = 0
            
            for ticket in all_tickets:
                try:
                    # فحص إذا كانت التذكرة موجودة
                    ticket_id = ticket.get('id')
                    cursor.execute("SELECT id FROM zoho_tickets_cache WHERE zoho_ticket_id = %s", (ticket_id,))
                    
                    if cursor.fetchone():
                        skipped_count += 1
                        continue  # التذكرة موجودة، تجاهل
                    
                    # استخراج البيانات
                    ticket_number = ticket.get('ticketNumber', '')
                    subject = ticket.get('subject', '')
                    status = ticket.get('status', 'Open')
                    created_time = ticket.get('createdTime', '')
                    closed_time = ticket.get('closedTime')
                    department_id = ticket.get('departmentId', '')
                    thread_count = ticket.get('threadCount', 0)
                    raw_data_json = json.dumps(ticket)
                    
                    # استخراج Closed By من raw_data
                    closed_by_value = None
                    
                    # البحث في cf
                    if 'cf' in ticket and isinstance(ticket['cf'], dict):
                        closed_by_value = ticket['cf'].get('cf_closed_by')
                    
                    # إذا لم توجد في cf، ابحث في customFields
                    if not closed_by_value:
                        if 'customFields' in ticket and isinstance(ticket['customFields'], dict):
                            closed_by_value = ticket['customFields'].get('Closed By')
                    
                    # إذا لم توجد، ابحث في المستوى العلوي
                    if not closed_by_value:
                        closed_by_value = ticket.get('cf_closed_by')
                    
                    # إدراج التذكرة مع closed_by_name
                    insert_query = """
                        INSERT INTO zoho_tickets_cache 
                        (zoho_ticket_id, ticket_number, subject, status, department_id, 
                         created_at_zoho, closed_at_zoho, thread_count, raw_data, closed_by_name)
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """
                    
                    # تحويل التاريخ
                    created_at = None
                    closed_at = None
                    
                    if created_time:
                        try:
                            created_at = datetime.fromisoformat(created_time.replace('Z', ''))
                        except:
                            created_at = datetime.now()
                    
                    if closed_time:
                        try:
                            closed_at = datetime.fromisoformat(closed_time.replace('Z', ''))
                        except:
                            closed_at = None
                    
                    cursor.execute(insert_query, (
                        ticket_id, ticket_number, subject, status, department_id,
                        created_at, closed_at, thread_count, raw_data_json, closed_by_value
                    ))
                    
                    saved_count += 1
                    
                    if closed_by_value:
                        self.logger.log(f"Ticket {ticket_number}: Extracted closed_by = {closed_by_value}")
                    
                except Exception as e:
                    self.logger.error(f"Failed to save ticket {ticket.get('id')}: {str(e)}")
            
            conn.commit()
            cursor.close()
            conn.close()
            
            progress_window.destroy()
            
            # عرض النتائج
            messagebox.showinfo(
                "تم الانتهاء",
                f"تم جلب {len(all_tickets)} تذكرة من Zoho\n\n"
                f"✅ تم حفظ {saved_count} تذكرة جديدة\n"
                f"⏭️ تم تجاهل {skipped_count} تذكرة موجودة (مكررة)\n"
                f"❌ فشل {len(all_tickets) - saved_count - skipped_count} تذكرة"
            )
            
            self.logger.success(
                f"Fetched {saved_count} new tickets from last {days} days. "
                f"Skipped {skipped_count} duplicates."
            )
            self.logger.log(f"Total tickets: {len(all_tickets)}, Saved: {saved_count}, Skipped: {skipped_count}, Failed: {len(all_tickets) - saved_count - skipped_count}")
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            progress_window.destroy()
            error_msg = f"حدث خطأ:\n{str(e)}"
            messagebox.showerror("خطأ", error_msg)
            self.logger.error(f"Failed to fetch tickets from Zoho: {str(e)}")
    
    def fetch_incremental_tickets(self):
        """جلب التذاكر المحدثة من آخر وقت حتى الآن بدون تكرار"""
        try:
            # الحصول على آخر وقت تم التحديث فيه
            conn = self.connect_db()
            if not conn:
                messagebox.showerror("خطأ", "لا يمكن الاتصال بقاعدة البيانات")
                return
            
            cursor = conn.cursor()
            
            # البحث عن آخر تذكرة محدثة (modifiedTime أو createdTime)
            query = """
                SELECT GREATEST(
                    MAX(created_at_zoho),
                    MAX(COALESCE(closed_at_zoho, '1970-01-01'::timestamp))
                ) as last_time
                FROM zoho_tickets_cache
            """
            
            cursor.execute(query)
            result = cursor.fetchone()
            last_time = result[0] if result and result[0] else None
            
            cursor.close()
            conn.close()
            
            if not last_time:
                messagebox.showinfo("معلومات", "لا توجد تذاكر في قاعدة البيانات. استخدم 'جلب تذاكر جديدة' لبدء الجلب.")
                return
            
            # طرح دقيقة واحدة للتأكد من عدم تفويت أي تذاكر
            start_date = last_time - timedelta(minutes=1)
            end_date = datetime.now()
            
            # عرض نافذة تأكيد
            confirm_msg = (
                f"سيتم جلب التذاكر المحدثة من:\n"
                f"من: {start_date.strftime('%Y-%m-%d %H:%M:%S')}\n"
                f"إلى: {end_date.strftime('%Y-%m-%d %H:%M:%S')}\n\n"
                f"هل تريد المتابعة؟"
            )
            
            if not messagebox.askyesno("تأكيد", confirm_msg):
                return
            
            # نافذة progress
            progress_window = tk.Toplevel(self.root)
            progress_window.title("جاري جلب التذاكر المحدثة...")
            progress_window.geometry("500x250")
            progress_window.transient(self.root)
            progress_window.grab_set()
            
            progress_label = tk.Label(
                progress_window,
                text="جاري جلب التذاكر المحدثة...",
                font=("Arial", 12, "bold"),
                pady=20
            )
            progress_label.pack()
            
            progress_bar = ttk.Progressbar(
                progress_window,
                length=400,
                mode='indeterminate'
            )
            progress_bar.pack(pady=10)
            progress_bar.start()
            
            status_label = tk.Label(
                progress_window,
                text="",
                font=("Arial", 10),
                fg="blue"
            )
            status_label.pack()
            
            progress_window.update()
            
            try:
                self.logger.log(f"Starting to fetch incremental tickets from {start_date} to {end_date}")
                
                # تنسيق التاريخ للـ API (ISO 8601)
                start_date_str = start_date.strftime('%Y-%m-%dT%H:%M:%S.000Z')
                end_date_str = end_date.strftime('%Y-%m-%dT%H:%M:%S.000Z')
                
                status_label.config(text="جاري الاتصال بـ Zoho...")
                progress_window.update()
                
                # الحصول على access token
                token = self.get_zoho_access_token()
                if not token:
                    raise Exception("Failed to get Zoho access token")
                
                # استخدام /tickets/search للتذاكر المحدثة
                url = f"{self.zoho_config['base_url']}/tickets/search"
                headers = {
                    'Authorization': f'Zoho-oauthtoken {token}',
                    'Content-Type': 'application/json'
                }
                
                self.logger.log(f"Searching tickets from {start_date_str} to {end_date_str}")
                
                # Loop لجلب التذاكر في صفحات
                all_tickets = []
                page = 1
                total_fetched = 0
                
                while True:
                    status_label.config(text=f"جاري جلب التذاكر... صفحة {page}")
                    progress_window.update()
                    
                    # إضافة offset لكل صفحة
                    offset = (page - 1) * 100
                    
                    params = {
                        'orgId': self.zoho_config['org_id'],
                        'limit': 100,
                        'from': offset,
                        'sortBy': '-modifiedTime',
                        'modifiedTimeRange': f"{start_date_str},{end_date_str}"
                    }
                    
                    self.logger.log(f"Fetching page {page} with offset {offset}")
                    
                    response = requests.get(url, headers=headers, params=params)
                    
                    if response.status_code != 200:
                        self.logger.error(f"API returned status {response.status_code}: {response.text[:200]}")
                        if response.status_code in [400, 422]:
                            self.logger.log("Reached end of tickets or invalid request")
                            break
                        raise Exception(f"API Error: {response.status_code} - {response.text[:200]}")
                    
                    data = response.json()
                    tickets = data.get('data', [])
                    
                    if not tickets or len(tickets) == 0:
                        self.logger.log(f"No more tickets found on page {page}")
                        break
                    
                    self.logger.log(f"Page {page}: Got {len(tickets)} tickets")
                    
                    # إضافة التذاكر
                    all_tickets.extend(tickets)
                    total_fetched += len(tickets)
                    
                    # إذا كانت عدد التذاكر أقل من 100، انتهينا
                    if len(tickets) < 100:
                        self.logger.log("Got less than 100 tickets, assuming end of data")
                        break
                    
                    page += 1
                    
                    # حد أقصى 50 صفحة (5000 تذكرة)
                    if page > 50:
                        self.logger.warning("Reached max pages limit (50 pages)")
                        break
                
                self.logger.log(f"Total fetched: {total_fetched} tickets from {page-1} pages")
                
                status_label.config(text=f"تم جلب {len(all_tickets)} تذكرة، جاري الحفظ...")
                progress_window.update()
                
                # حفظ في قاعدة البيانات
                conn = self.connect_db()
                if not conn:
                    raise Exception("Cannot connect to database")
                
                cursor = conn.cursor()
                saved_count = 0
                updated_count = 0
                skipped_count = 0
                
                for ticket in all_tickets:
                    try:
                        # فحص إذا كانت التذكرة موجودة
                        ticket_id = ticket.get('id')
                        cursor.execute("SELECT id FROM zoho_tickets_cache WHERE zoho_ticket_id = %s", (ticket_id,))
                        
                        existing = cursor.fetchone()
                        if existing:
                            # التذكرة موجودة، تحديثها
                            ticket_number = ticket.get('ticketNumber', '')
                            subject = ticket.get('subject', '')
                            status_val = ticket.get('status', 'Open')
                            created_time = ticket.get('createdTime', '')
                            closed_time = ticket.get('closedTime')
                            department_id = ticket.get('departmentId', '')
                            thread_count = ticket.get('threadCount', 0)
                            raw_data_json = json.dumps(ticket)
                            
                            # استخراج Closed By من raw_data
                            closed_by_value = None
                            if 'cf' in ticket and isinstance(ticket['cf'], dict):
                                closed_by_value = ticket['cf'].get('cf_closed_by')
                            
                            if not closed_by_value and 'customFields' in ticket:
                                for cf in ticket['customFields']:
                                    if cf.get('apiName') == 'cf_closed_by':
                                        closed_by_value = cf.get('value')
                                        break
                            
                            # تحويل التاريخ
                            created_at = None
                            closed_at = None
                            
                            if created_time:
                                try:
                                    created_at = datetime.fromisoformat(created_time.replace('Z', ''))
                                except:
                                    created_at = datetime.now()
                            
                            if closed_time:
                                try:
                                    closed_at = datetime.fromisoformat(closed_time.replace('Z', ''))
                                except:
                                    closed_at = None
                            
                            # تحديث التذكرة
                            update_query = """
                                UPDATE zoho_tickets_cache 
                                SET ticket_number = %s, subject = %s, status = %s, department_id = %s,
                                    created_at_zoho = %s, closed_at_zoho = %s, thread_count = %s, 
                                    raw_data = %s, closed_by_name = %s, updated_at = NOW()
                                WHERE zoho_ticket_id = %s
                            """
                            
                            cursor.execute(update_query, (
                                ticket_number, subject, status_val, department_id,
                                created_at, closed_at, thread_count, raw_data_json, closed_by_value, ticket_id
                            ))
                            
                            updated_count += 1
                            continue
                        
                        # استخراج البيانات
                        ticket_number = ticket.get('ticketNumber', '')
                        subject = ticket.get('subject', '')
                        status_val = ticket.get('status', 'Open')
                        created_time = ticket.get('createdTime', '')
                        closed_time = ticket.get('closedTime')
                        department_id = ticket.get('departmentId', '')
                        thread_count = ticket.get('threadCount', 0)
                        raw_data_json = json.dumps(ticket)
                        
                        # استخراج Closed By من raw_data
                        closed_by_value = None
                        if 'cf' in ticket and isinstance(ticket['cf'], dict):
                            closed_by_value = ticket['cf'].get('cf_closed_by')
                        
                        if not closed_by_value and 'customFields' in ticket:
                            for cf in ticket['customFields']:
                                if cf.get('apiName') == 'cf_closed_by':
                                    closed_by_value = cf.get('value')
                                    break
                        
                        # تحويل التاريخ
                        created_at = None
                        closed_at = None
                        
                        if created_time:
                            try:
                                created_at = datetime.fromisoformat(created_time.replace('Z', ''))
                            except:
                                created_at = datetime.now()
                        
                        if closed_time:
                            try:
                                closed_at = datetime.fromisoformat(closed_time.replace('Z', ''))
                            except:
                                closed_at = None
                        
                        # إدراج التذكرة
                        insert_query = """
                            INSERT INTO zoho_tickets_cache 
                            (zoho_ticket_id, ticket_number, subject, status, department_id, 
                             created_at_zoho, closed_at_zoho, thread_count, raw_data, closed_by_name)
                            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                        """
                        
                        cursor.execute(insert_query, (
                            ticket_id, ticket_number, subject, status_val, department_id,
                            created_at, closed_at, thread_count, raw_data_json, closed_by_value
                        ))
                        
                        saved_count += 1
                        
                    except Exception as e:
                        self.logger.error(f"Failed to save/update ticket {ticket.get('id')}: {str(e)}")
                        skipped_count += 1
                
                conn.commit()
                cursor.close()
                conn.close()
                
                progress_window.destroy()
                
                # عرض النتائج
                messagebox.showinfo(
                    "تم الانتهاء",
                    f"تم جلب {len(all_tickets)} تذكرة من Zoho\n\n"
                    f"✅ تم حفظ {saved_count} تذكرة جديدة\n"
                    f"🔄 تم تحديث {updated_count} تذكرة موجودة\n"
                    f"⏭️ تم تجاهل {skipped_count} تذكرة"
                )
                
                self.logger.success(
                    f"Incremental fetch completed: {saved_count} new, {updated_count} updated, {skipped_count} skipped"
                )
                
                # إعادة تحميل البيانات
                self.load_data()
                
            except Exception as e:
                progress_window.destroy()
                error_msg = f"حدث خطأ:\n{str(e)}"
                messagebox.showerror("خطأ", error_msg)
                self.logger.error(f"Failed to fetch incremental tickets: {str(e)}")
                
        except Exception as e:
            error_msg = f"حدث خطأ:\n{str(e)}"
            messagebox.showerror("خطأ", error_msg)
            self.logger.error(f"Failed to fetch incremental tickets: {str(e)}")
    
    def show_context_menu(self, event):
        """عرض قائمة منبثقة عند النقر بالزر الأيمن"""
        try:
            item = self.tree.identify_row(event.y)
            if item:
                self.tree.selection_set(item)
                self.context_menu.post(event.x_root, event.y_root)
        except:
            pass
    
    def copy_selected(self, event=None):
        """نسخ النص المحدد عند الضغط على Ctrl+C"""
        return self.copy_selected_row()
    
    def copy_selected_row(self):
        """نسخ الصف المحدد"""
        selected = self.tree.selection()
        if not selected:
            messagebox.showinfo("معلومات", "لم يتم تحديد أي صف للنسخ")
            return
        
        try:
            copied_texts = []
            for item in selected:
                values = self.tree.item(item, 'values')
                if values:
                    # نسخ جميع القيم مفصولة بـ tab
                    text = '\t'.join(str(v) for v in values)
                    copied_texts.append(text)
            
            if copied_texts:
                text_to_copy = '\n'.join(copied_texts)
                if clipboard:
                    clipboard.clear()
                    clipboard.copy(text_to_copy)
                    messagebox.showinfo("تم النسخ", f"تم نسخ {len(copied_texts)} صف بنجاح")
                else:
                    messagebox.showerror("خطأ", "لا يمكن الوصول إلى clipboard")
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ عند النسخ:\n{str(e)}")
    
    def copy_all_data(self):
        """نسخ جميع البيانات من الصفوف المرئية"""
        try:
            visible_items = self.tree.get_children()
            if not visible_items:
                messagebox.showinfo("معلومات", "لا توجد بيانات للنسخ")
                return
            
            copied_texts = []
            for item in visible_items:
                values = self.tree.item(item, 'values')
                if values:
                    text = '\t'.join(str(v) for v in values)
                    copied_texts.append(text)
            
            if copied_texts:
                text_to_copy = '\n'.join(copied_texts)
                if clipboard:
                    clipboard.clear()
                    clipboard.copy(text_to_copy)
                    messagebox.showinfo("تم النسخ", f"تم نسخ {len(copied_texts)} صف بنجاح")
                else:
                    messagebox.showerror("خطأ", "لا يمكن الوصول إلى clipboard")
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
    
    def paste_from_clipboard(self):
        """قراءة البيانات من clipboard وإدراجها في قاعدة البيانات"""
        if not clipboard:
            messagebox.showerror("خطأ", "لا يمكن الوصول إلى clipboard")
            return
        
        try:
            # قراءة البيانات من clipboard
            clipboard_text = clipboard.paste()
            
            if not clipboard_text or not clipboard_text.strip():
                messagebox.showwarning("تحذير", "لا توجد بيانات في clipboard")
                return
            
            # تحليل البيانات
            lines = clipboard_text.strip().split('\n')
            
            if len(lines) < 1:
                messagebox.showwarning("تحذير", "لا توجد بيانات صالحة في clipboard")
                return
            
            # عرض نافذة تأكيد
            confirm = messagebox.askyesno(
                "تأكيد",
                f"تم العثور على {len(lines)} صف في clipboard.\n\n"
                "هل تريد إدراجها في قاعدة البيانات؟"
            )
            
            if not confirm:
                return
            
            # نافذة progress
            progress_window = tk.Toplevel(self.root)
            progress_window.title("جاري المعالجة...")
            progress_window.geometry("500x150")
            progress_window.transient(self.root)
            progress_window.grab_set()
            
            progress_label = tk.Label(
                progress_window,
                text="جاري المعالجة...",
                font=("Arial", 12),
                pady=20
            )
            progress_label.pack()
            
            progress_bar = ttk.Progressbar(
                progress_window,
                length=400,
                mode='determinate',
                maximum=len(lines)
            )
            progress_bar.pack(pady=10)
            
            progress_window.update()
            
            # الاتصال بقاعدة البيانات
            conn = self.connect_db()
            if not conn:
                progress_window.destroy()
                messagebox.showerror("خطأ", "لا يمكن الاتصال بقاعدة البيانات")
                return
            
            cursor = conn.cursor()
            inserted_count = 0
            skipped_count = 0
            error_count = 0
            
            for i, line in enumerate(lines, 1):
                try:
                    # تحديث progress
                    progress_label.config(text=f"جاري المعالجة... ({i}/{len(lines)})")
                    progress_bar['value'] = i
                    progress_window.update()
                    
                    # تخطي الصف الأول إذا كان عنوان
                    if i == 1 and line.upper().startswith('ZOHO'):
                        continue
                    
                    # تقسيم السطر
                    parts = [p.strip() for p in line.split('\t')]
                    
                    if len(parts) < 5:
                        error_count += 1
                        continue
                    
                    # محاولة استخراج البيانات
                    try:
                        zoho_ticket_id = parts[1] if len(parts) > 1 else None
                        ticket_number = parts[2] if len(parts) > 2 else None
                        closed_by_name = parts[4] if len(parts) > 4 else None
                        subject = parts[5] if len(parts) > 5 else None
                        status = parts[6] if len(parts) > 6 else None
                        
                        # التحقق من وجود التذكرة
                        cursor.execute("SELECT id FROM zoho_tickets_cache WHERE zoho_ticket_id = %s", (zoho_ticket_id,))
                        if cursor.fetchone():
                            skipped_count += 1
                            continue
                        
                        # إدراج التذكرة الجديدة
                        # ملاحظة: هذا مثال بسيط - يجب تكيفه مع بنية البيانات الفعلية
                        insert_query = """
                            INSERT INTO zoho_tickets_cache 
                            (zoho_ticket_id, ticket_number, closed_by_name, subject, status)
                            VALUES (%s, %s, %s, %s, %s)
                        """
                        
                        cursor.execute(insert_query, (
                            zoho_ticket_id,
                            ticket_number,
                            closed_by_name,
                            subject,
                            status
                        ))
                        
                        inserted_count += 1
                        
                    except psycopg2.IntegrityError:
                        skipped_count += 1
                    except Exception as e:
                        error_count += 1
                        self.logger.error(f"Error processing line {i}: {str(e)}")
                        
                except Exception as e:
                    error_count += 1
                    self.logger.error(f"Error processing line {i}: {str(e)}")
            
            conn.commit()
            cursor.close()
            conn.close()
            
            progress_window.destroy()
            
            # عرض النتائج
            messagebox.showinfo(
                "تم الانتهاء",
                f"تم إدراج {inserted_count} تذكرة جديدة\n"
                f"تجاهل {skipped_count} تذكرة موجودة\n"
                f"فشل {error_count} تذكرة"
            )
            
            self.logger.success(f"Pasted from clipboard: {inserted_count} inserted, {skipped_count} skipped, {error_count} errors")
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
            self.logger.error(f"Error pasting from clipboard: {str(e)}")
    
    def update_ticket_status_via_api(self, ticket_id, status):
        """تحديث حالة التذكرة عبر API في Zoho وفي قاعدة البيانات"""
        import requests
        
        try:
            # استدعاء API لتحديث الحالة
            url = f"http://localhost:8000/api/zoho/ticket/{ticket_id}/status"
            data = {'status': status}
            
            response = requests.put(url, json=data, timeout=10)
            
            if response.status_code == 200:
                result = response.json()
                return result.get('success', False), result.get('data', {})
            else:
                self.logger.error(f"API request failed: {response.status_code}")
                return False, {}
                
        except requests.exceptions.RequestException as e:
            self.logger.error(f"Error calling API: {str(e)}")
            return False, {}
    
    def change_ticket_to_open(self):
        """تغيير حالة التذاكر المحددة إلى Open"""
        selected = self.tree.selection()
        
        if not selected:
            messagebox.showwarning("تحذير", "يرجى تحديد صف أو أكثر لتغيير حالتها")
            return
        
        # تأكيد من المستخدم
        if not messagebox.askyesno(
            "تأكيد",
            f"هل تريد تغيير حالة التذاكر المحددة ({len(selected)} تذكرة) إلى 'Open'؟\n\nسيتم التحديث في Zoho وفي قاعدة البيانات المحلية."
        ):
            return
        
        # جمع معلومات التذاكر المحددة
        tickets_to_update = []
        for item in selected:
            if item not in self.full_data:
                continue
            row_data = self.full_data[item]
            ticket_id = row_data[1] if len(row_data) > 1 else None
            
            if ticket_id:
                tickets_to_update.append({
                    'item': item,
                    'ticket_id': ticket_id,
                    'ticket_number': row_data[2] if len(row_data) > 2 else 'N/A'
                })
        
        if not tickets_to_update:
            messagebox.showerror("خطأ", "لا توجد تذاكر صالحة للتحديث")
            return
        
        # تحديث قاعدة البيانات وZoho
        try:
            conn = self.connect_db()
            if not conn:
                raise Exception("لا يمكن الاتصال بقاعدة البيانات")
            
            cursor = conn.cursor()
            updated_count = 0
            zoho_updated_count = 0
            failed_count = 0
            
            for ticket_info in tickets_to_update:
                try:
                    # تحديث في Zoho و قاعدة البيانات عبر API
                    success, result = self.update_ticket_status_via_api(
                        ticket_info['ticket_id'], 
                        'Open'
                    )
                    
                    if success:
                        updated_count += 1
                        if result.get('zoho_updated', False):
                            zoho_updated_count += 1
                        
                        self.logger.log(
                            f"Changed ticket {ticket_info['ticket_number']} to Open "
                            f"(DB: ✓, Zoho: {'✓' if result.get('zoho_updated') else '✗'})"
                        )
                    else:
                        # إذا فشل API، حدث قاعدة البيانات فقط
                        update_query = """
                            UPDATE zoho_tickets_cache 
                            SET status = 'Open',
                                updated_at = CURRENT_TIMESTAMP
                            WHERE zoho_ticket_id = %s
                        """
                        cursor.execute(update_query, (ticket_info['ticket_id'],))
                        conn.commit()
                        updated_count += 1
                        self.logger.log(f"Changed ticket {ticket_info['ticket_number']} to Open (DB only)")
                    
                except Exception as e:
                    failed_count += 1
                    self.logger.error(f"Failed to update ticket {ticket_info['ticket_id']}: {str(e)}")
            
            cursor.close()
            conn.close()
            
            result_msg = f"تم تغيير حالة {updated_count} تذكرة إلى 'Open' بنجاح!"
            if zoho_updated_count > 0:
                result_msg += f"\nتم التحديث في Zoho لـ {zoho_updated_count} تذكرة"
            if failed_count > 0:
                result_msg += f"\nفشل {failed_count} تذكرة"
            
            messagebox.showinfo("نجاح", result_msg)
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
            self.logger.error(f"Error changing ticket status: {str(e)}")
    
    def change_ticket_to_closed(self):
        """تغيير حالة التذاكر المحددة إلى Closed"""
        selected = self.tree.selection()
        
        if not selected:
            messagebox.showwarning("تحذير", "يرجى تحديد صف أو أكثر لتغيير حالتها")
            return
        
        # تأكيد من المستخدم
        if not messagebox.askyesno(
            "تأكيد",
            f"هل تريد تغيير حالة التذاكر المحددة ({len(selected)} تذكرة) إلى 'Closed'؟\n\nسيتم التحديث في Zoho وفي قاعدة البيانات المحلية."
        ):
            return
        
        # جمع معلومات التذاكر المحددة
        tickets_to_update = []
        for item in selected:
            if item not in self.full_data:
                continue
            row_data = self.full_data[item]
            ticket_id = row_data[1] if len(row_data) > 1 else None
            
            if ticket_id:
                tickets_to_update.append({
                    'item': item,
                    'ticket_id': ticket_id,
                    'ticket_number': row_data[2] if len(row_data) > 2 else 'N/A'
                })
        
        if not tickets_to_update:
            messagebox.showerror("خطأ", "لا توجد تذاكر صالحة للتحديث")
            return
        
        # تحديث قاعدة البيانات وZoho
        try:
            conn = self.connect_db()
            if not conn:
                raise Exception("لا يمكن الاتصال بقاعدة البيانات")
            
            cursor = conn.cursor()
            updated_count = 0
            zoho_updated_count = 0
            failed_count = 0
            
            for ticket_info in tickets_to_update:
                try:
                    # تحديث في Zoho و قاعدة البيانات عبر API
                    success, result = self.update_ticket_status_via_api(
                        ticket_info['ticket_id'], 
                        'Closed'
                    )
                    
                    if success:
                        updated_count += 1
                        if result.get('zoho_updated', False):
                            zoho_updated_count += 1
                        
                        self.logger.log(
                            f"Changed ticket {ticket_info['ticket_number']} to Closed "
                            f"(DB: ✓, Zoho: {'✓' if result.get('zoho_updated') else '✗'})"
                        )
                    else:
                        # إذا فشل API، حدث قاعدة البيانات فقط
                        update_query = """
                            UPDATE zoho_tickets_cache 
                            SET status = 'Closed',
                                updated_at = CURRENT_TIMESTAMP
                            WHERE zoho_ticket_id = %s
                        """
                        cursor.execute(update_query, (ticket_info['ticket_id'],))
                        conn.commit()
                        updated_count += 1
                        self.logger.log(f"Changed ticket {ticket_info['ticket_number']} to Closed (DB only)")
                    
                except Exception as e:
                    failed_count += 1
                    self.logger.error(f"Failed to update ticket {ticket_info['ticket_id']}: {str(e)}")
            
            cursor.close()
            conn.close()
            
            result_msg = f"تم تغيير حالة {updated_count} تذكرة إلى 'Closed' بنجاح!"
            if zoho_updated_count > 0:
                result_msg += f"\nتم التحديث في Zoho لـ {zoho_updated_count} تذكرة"
            if failed_count > 0:
                result_msg += f"\nفشل {failed_count} تذكرة"
            
            messagebox.showinfo("نجاح", result_msg)
            
            # إعادة تحميل البيانات
            self.load_data()
            
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ:\n{str(e)}")
            self.logger.error(f"Error changing ticket status to Closed: {str(e)}")
    
    def show_ticket_threads(self):
        """عرض محادثات التذكرة المحددة"""
        import requests
        
        selected = self.tree.selection()
        
        if not selected:
            messagebox.showwarning("تحذير", "يرجى تحديد تذكرة لعرض محادثاتها")
            return
        
        if len(selected) > 1:
            messagebox.showwarning("تحذير", "يرجى تحديد تذكرة واحدة فقط")
            return
        
        # جمع معلومات التذكرة المحددة
        item = selected[0]
        if item not in self.full_data:
            messagebox.showerror("خطأ", "لا يمكن العثور على بيانات التذكرة")
            return
        
        row_data = self.full_data[item]
        ticket_id = row_data[1] if len(row_data) > 1 else None
        ticket_number = row_data[2] if len(row_data) > 2 else 'N/A'
        
        if not ticket_id:
            messagebox.showerror("خطأ", "لا يمكن العثور على معرف التذكرة")
            return
        
        # إنشاء نافذة لعرض المحادثات
        threads_window = tk.Toplevel(self.root)
        threads_window.title(f"محادثات التذكرة #{ticket_number}")
        threads_window.geometry("900x700")
        
        # Label للتحميل
        loading_label = tk.Label(
            threads_window, 
            text="جاري جلب المحادثات...", 
            font=("Arial", 12)
        )
        loading_label.pack(pady=20)
        
        threads_window.update()
        
        try:
            # محاولة جلب المحادثات من API أولاً
            threads = None
            
            try:
                # محاولة استخدام desktop API بدون auth
                url = f"http://localhost:8000/api/zoho/desktop/ticket/{ticket_id}/threads"
                response = requests.get(url, timeout=30)
                
                if response.status_code == 401:
                    raise Exception("يحتاج الـ API إلى تسجيل دخول\nيرجى فتح المتصفح على http://127.0.0.1:8000 وتسجيل الدخول أولاً")
                elif response.status_code == 404:
                    raise Exception(f"لم يتم العثور على التذكرة {ticket_id}")
                elif response.status_code != 200:
                    raise Exception(f"خطأ في استدعاء API: {response.status_code}\n{response.text[:200]}")
                
                # محاولة تحليل JSON
                try:
                    result = response.json()
                except json.JSONDecodeError as e:
                    self.logger.error(f"Invalid JSON response: {response.text[:500]}")
                    raise Exception(f"رد غير صحيح من الـ API: {str(e)}")
                
                if not result.get('success', False):
                    raise Exception(result.get('error', 'خطأ غير معروف'))
                
                threads = result.get('threads', [])
                
                # التأكد من أن threads هو list
                if not isinstance(threads, list):
                    self.logger.error(f"Threads is not a list: {type(threads)} - {threads}")
                    raise Exception(f"خطأ: المحادثات في صيغة غير صحيحة")
                
                # Log first thread for debugging
                if threads and len(threads) > 0:
                    self.logger.log(f"First thread data: {json.dumps(threads[0], ensure_ascii=False)[:500]}")
                
                self.logger.log(f"Fetched {len(threads)} threads from API for ticket {ticket_number}")
                
                # إزالة رسالة التحميل
                loading_label.destroy()
                    
            except (requests.exceptions.ConnectionError, requests.exceptions.Timeout, Exception) as api_error:
                # إذا فشل API، جرب Zoho API مباشرة
                self.logger.log(f"Laravel API failed: {str(api_error)}")
                self.logger.log("Trying Zoho API directly...")
                
                loading_label.config(text="جاري الاتصال بـ Zoho مباشرة...")
                threads_window.update()
                
                try:
                    # الحصول على access token من Zoho
                    token = self.get_zoho_access_token()
                    if not token:
                        raise Exception("Failed to get Zoho access token")
                    
                    # استدعاء Zoho API مباشرة
                    zoho_url = f"{self.zoho_config['base_url']}/tickets/{ticket_id}/threads"
                    headers = {
                        'Authorization': f'Zoho-oauthtoken {token}',
                        'orgId': self.zoho_config['org_id'],
                        'Content-Type': 'application/json'
                    }
                    
                    response = requests.get(zoho_url, headers=headers, timeout=30)
                    
                    if response.status_code == 200:
                        data = response.json()
                        if 'data' in data:
                            threads = data['data']
                            self.logger.log(f"Fetched {len(threads)} threads directly from Zoho for ticket {ticket_number}")
                        else:
                            threads = []
                        
                        # إزالة رسالة التحميل
                        loading_label.destroy()
                    else:
                        raise Exception(f"Zoho API returned {response.status_code}")
                        
                except Exception as zoho_error:
                    # إذا فشل Zoho API أيضاً، أظهر رسالة
                    self.logger.log(f"Zoho API also failed: {str(zoho_error)}")
                    
                    # إزالة loading_label واظهر رسالة خطأ
                    loading_label.destroy()
                    
                    error_label = tk.Label(
                        threads_window,
                        text=f"تعذر جلب المحادثات من جميع المصادر\n\n"
                             f"خطأ Laravel API: {str(api_error)[:100]}...\n\n"
                             f"خطأ Zoho API: {str(zoho_error)[:100]}...\n\n"
                             f"⚠️ الحلول المقترحة:\n"
                             f"1. تأكد أن Laravel يعمل: php artisan serve\n"
                             f"2. أو استخدم زر '🔄 تحديث من Zoho' لتحديث البيانات",
                        font=("Arial", 10),
                        fg="red",
                        wraplength=800,
                        justify="center",
                        bg="#fff3cd"
                    )
                    error_label.pack(pady=50, padx=20)
                    
                    threads = []
                    return  # خرج من الدالة لأن تم عرض رسالة خطأ
            
            # التحقق من وجود محادثات
            if not threads:
                loading_label.destroy()
                no_threads_label = tk.Label(
                    threads_window,
                    text="لا توجد محادثات لهذه التذكرة",
                    font=("Arial", 12),
                    fg="gray"
                )
                no_threads_label.pack(pady=50)
                return
            
            # إنشاء ScrolledText
            text_widget = scrolledtext.ScrolledText(
                threads_window,
                wrap=tk.WORD,
                font=("Consolas", 10),
                bg="white",
                fg="black"
            )
            text_widget.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
            
            # عرض المحادثات
            for idx, thread in enumerate(threads, 1):
                # التحقق من أن thread هو dict
                if not isinstance(thread, dict):
                    self.logger.error(f"Thread is not a dict: {type(thread)} - {thread}")
                    text_widget.insert(tk.END, f"\n{'='*80}\n", 'separator')
                    text_widget.insert(tk.END, f"المحادثة #{idx} - خطأ في البيانات\n", 'header')
                    text_widget.insert(tk.END, f"نوع البيانات: {type(thread)}\n", 'label')
                    text_widget.insert(tk.END, f"البيانات: {str(thread)[:200]}\n", 'content')
                    continue
                
                # معلومات أساسية
                try:
                    # محاولة جلب from
                    from_email = 'غير محدد'
                    
                    # طريقة 1: من fromEmailAddress
                    if 'fromEmailAddress' in thread:
                        from_data = thread.get('fromEmailAddress', {})
                        if isinstance(from_data, dict):
                            from_email = from_data.get('emailAddress', from_data.get('email', 'غير محدد'))
                        elif isinstance(from_data, str):
                            from_email = from_data
                    
                    # طريقة 2: من mailFrom
                    if from_email == 'غير محدد' and 'mailFrom' in thread:
                        mail_from = thread.get('mailFrom', {})
                        if isinstance(mail_from, dict):
                            from_email = mail_from.get('emailAddress', mail_from.get('email', 'غير محدد'))
                        elif isinstance(mail_from, str):
                            from_email = mail_from
                    
                    # طريقة 3: from مباشر
                    if from_email == 'غير محدد' and 'from' in thread:
                        from_data = thread.get('from')
                        if isinstance(from_data, dict):
                            from_email = from_data.get('emailAddress', from_data.get('email', 'غير محدد'))
                        elif isinstance(from_data, str):
                            from_email = from_data
                    
                except Exception as e:
                    self.logger.error(f"Error parsing From: {e}")
                    from_email = 'غير محدد'
                
                try:
                    to_list = 'غير محدد'
                    
                    # طريقة 1: من toEmailAddressList
                    if 'toEmailAddressList' in thread:
                        to_emails = thread.get('toEmailAddressList', [])
                        if isinstance(to_emails, list):
                            to_list = ', '.join([email.get('emailAddress', email.get('email', str(email))) if isinstance(email, dict) else str(email) for email in to_emails if email])
                    
                    # طريقة 2: من toMailList
                    if to_list == 'غير محدد' and 'toMailList' in thread:
                        to_emails = thread.get('toMailList', [])
                        if isinstance(to_emails, list):
                            to_list = ', '.join([email.get('emailAddress', email.get('email', str(email))) if isinstance(email, dict) else str(email) for email in to_emails if email])
                    
                    # طريقة 3: من to مباشر
                    if to_list == 'غير محدد' and 'to' in thread:
                        to_data = thread.get('to')
                        if isinstance(to_data, list):
                            to_list = ', '.join([str(t) for t in to_data if t])
                        elif isinstance(to_data, dict):
                            to_list = to_data.get('emailAddress', to_data.get('email', 'غير محدد'))
                        elif isinstance(to_data, str):
                            to_list = to_data
                    
                except Exception as e:
                    self.logger.error(f"Error parsing To: {e}")
                    to_list = 'غير محدد'
                
                direction = thread.get('direction', 'in')
                channel = thread.get('channel', 'EMAIL')
                created_time = thread.get('createdTime', '')
                subject = thread.get('subject', '')
                
                # المحتوى
                try:
                    # محاولة جلب المحتوى من مصادر مختلفة
                    content = ''
                    
                    # طريقة 1: من body
                    if 'body' in thread:
                        body_data = thread.get('body')
                        if isinstance(body_data, dict):
                            content = body_data.get('content', body_data.get('text', ''))
                        elif isinstance(body_data, str):
                            content = body_data
                    
                    # طريقة 2: من content
                    if not content and 'content' in thread:
                        content = thread.get('content', '')
                    
                    # طريقة 3: من fullContent
                    if not content and 'fullContent' in thread:
                        content = thread.get('fullContent', '')
                    
                    # طريقة 4: من summary
                    if not content and 'summary' in thread:
                        content = thread.get('summary', '')
                    
                    # إذا لم نجد محتوى، استخدم نص افتراضي
                    if not content or content.strip() == '':
                        content = 'لا يوجد محتوى'
                    elif not isinstance(content, str):
                        content = str(content)
                    
                    # التحقق من HTML
                    is_html = thread.get('isHtml', False) or (thread.get('contentType', '') == 'html') or ('<' in content and '>' in content and content.count('<') > 2)
                    
                except Exception as e:
                    self.logger.error(f"Error reading content: {e}")
                    content = 'لا يوجد محتوى'
                    is_html = False
                
                # عرض العنوان
                direction_text = "📥 وارد" if direction == 'in' else "📤 صادر"
                text_widget.insert(tk.END, f"\n{'='*80}\n", 'separator')
                text_widget.insert(tk.END, f"المحادثة #{idx} - {direction_text} - {channel}\n\n", 'header')
                
                # معلومات الإرسال
                text_widget.insert(tk.END, f"From: {from_email}\n", 'label')
                text_widget.insert(tk.END, f"To: {to_list}\n", 'label')
                
                if subject:
                    text_widget.insert(tk.END, f"Subject: {subject}\n", 'label')
                
                if created_time:
                    text_widget.insert(tk.END, f"Time: {created_time}\n", 'label')
                
                text_widget.insert(tk.END, "\n" + "-"*80 + "\n\n", 'separator')
                
                # محاولة جلب المحتوى المحسن من API (max-content endpoint) - مثل الـ Web interface
                try:
                    thread_id = thread.get('id', '')
                    if thread_id:
                        enhanced_url = f"http://localhost:8000/api/zoho/threads/{ticket_id}/{thread_id}/max-content"
                        try:
                            enhanced_response = requests.get(enhanced_url, timeout=10)
                            if enhanced_response.status_code == 200:
                                enhanced_data = enhanced_response.json()
                                if enhanced_data.get('success') and enhanced_data.get('data'):
                                    # استخدام المحتوى المحسن
                                    enhanced_thread = enhanced_data.get('data', {})
                                    enhanced_content = enhanced_thread.get('fullContent', '')
                                    if enhanced_content:
                                        content = enhanced_content
                                        self.logger.log(f"Loaded enhanced content for thread {thread_id}")
                        except:
                            pass  # Fallback إلى المحتوى العادي
                except:
                    pass
                
                # عرض المحتوى - الكامل بدون قطع مع الحفاظ على الـ Signature
                if is_html:
                    # تنظيف HTML بشكل ذكي للحفاظ على الـ Signatures
                    import re
                    
                    # حفظ الـ Signatures قبل التنظيف (عادة تكون بعد ----- أو في نهاية الـ email)
                    # تنظيف HTML لكن الحفاظ على الترتيب والمحتوى
                    content_clean = content
                    
                    # استبدال <br> و variants برسائل سطر جديدة
                    content_clean = re.sub(r'<br\s*/?>', '\n', content_clean, flags=re.IGNORECASE)
                    
                    # استبدال <p> بنقطة سطر جديدة
                    content_clean = re.sub(r'<p[^>]*>', '', content_clean, flags=re.IGNORECASE)
                    content_clean = content_clean.replace('</p>', '\n\n')
                    
                    # استبدال <div> بالسطر
                    content_clean = re.sub(r'<div[^>]*>', '\n', content_clean, flags=re.IGNORECASE)
                    content_clean = content_clean.replace('</div>', '')
                    
                    # إزالة بقية الـ HTML tags لكن الحفاظ على النص
                    content_clean = re.sub(r'<[^>]+>', '', content_clean)
                    
                    # تنظيف الـ HTML entities
                    content_clean = content_clean.replace('&nbsp;', ' ')
                    content_clean = content_clean.replace('&amp;', '&')
                    content_clean = content_clean.replace('&lt;', '<')
                    content_clean = content_clean.replace('&gt;', '>')
                    content_clean = content_clean.replace('&quot;', '"')
                    
                    # تنظيف المسافات الزائدة (لكن نحتفظ بـ 3 نصوص فارغة للفصل بين الأقسام)
                    content_clean = re.sub(r'\n{4,}', '\n\n\n', content_clean)
                    
                    # تنظيف المسافات المتعددة لكن نحتفظ ببعض للـ formatting
                    content_clean = re.sub(r' {3,}', '  ', content_clean)
                    
                    text_widget.insert(tk.END, f"{content_clean}\n", 'content')
                else:
                    # عرض المحتوى كما هو - كامل مع الـ Signature
                    text_widget.insert(tk.END, f"{content}\n", 'content')
                
                text_widget.insert(tk.END, "\n")
            
            # إضافة tags للألوان
            text_widget.tag_config('separator', foreground='gray')
            text_widget.tag_config('header', font=("Arial", 11, "bold"), foreground='#0066cc')
            text_widget.tag_config('label', foreground='#006600')
            text_widget.tag_config('content', foreground='#000000')
            text_widget.tag_config('html_note', foreground='#cc6600', font=("Arial", 9, "italic"))
            
            # تسجيل النجاح
            self.logger.log(f"Displayed {len(threads)} threads for ticket {ticket_number}")
            
            # إضافة أزرار للحفظ
            button_frame = tk.Frame(threads_window)
            button_frame.pack(pady=10)
            
            save_button = tk.Button(
                button_frame,
                text="💾 حفظ المحادثات",
                command=lambda: self.save_threads_to_file(ticket_number, threads)
            )
            save_button.pack(side=tk.LEFT, padx=5)
            
            export_html_button = tk.Button(
                button_frame,
                text="🌐 تصدير HTML",
                command=lambda: self.export_threads_html(ticket_number, threads)
            )
            export_html_button.pack(side=tk.LEFT, padx=5)
            
        except Exception as e:
            loading_label.destroy()
            error_label = tk.Label(
                threads_window,
                text=f"حدث خطأ في جلب المحادثات:\n{str(e)}",
                font=("Arial", 10),
                fg="red",
                wraplength=800,
                justify="center"
            )
            error_label.pack(pady=50, padx=20)
            self.logger.error(f"Error showing threads: {str(e)}")
    
    def save_threads_to_file(self, ticket_number, threads):
        """حفظ المحادثات في ملف نصي"""
        try:
            from tkinter import filedialog
            
            filename = f"threads_{ticket_number}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.txt"
            
            file_path = filedialog.asksaveasfilename(
                defaultextension=".txt",
                filetypes=[("Text files", "*.txt"), ("All files", "*.*")],
                initialfile=filename
            )
            
            if file_path:
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(f"محادثات التذكرة #{ticket_number}\n")
                    f.write("="*80 + "\n\n")
                    
                    for idx, thread in enumerate(threads, 1):
                        # التحقق من نوع thread
                        if not isinstance(thread, dict):
                            continue
                        
                        # استخراج From
                        try:
                            from_email = 'غير محدد'
                            if 'fromEmailAddress' in thread:
                                from_data = thread.get('fromEmailAddress', {})
                                if isinstance(from_data, dict):
                                    from_email = from_data.get('emailAddress', from_data.get('email', 'غير محدد'))
                                elif isinstance(from_data, str):
                                    from_email = from_data
                        except:
                            from_email = 'غير محدد'
                        
                        # استخراج To
                        try:
                            to_list = 'غير محدد'
                            if 'toEmailAddressList' in thread:
                                to_emails = thread.get('toEmailAddressList', [])
                                if isinstance(to_emails, list):
                                    to_list = ', '.join([email.get('emailAddress', email.get('email', str(email))) if isinstance(email, dict) else str(email) for email in to_emails if email])
                        except:
                            to_list = 'غير محدد'
                        
                        direction = thread.get('direction', 'in')
                        channel = thread.get('channel', 'EMAIL')
                        created_time = thread.get('createdTime', '')
                        subject = thread.get('subject', '')
                        
                        # استخراج Content - البحث في مصادر متعددة للحصول على المحتوى الكامل
                        try:
                            content = ''
                            # 1. من body
                            if 'body' in thread:
                                body_data = thread.get('body')
                                if isinstance(body_data, dict):
                                    content = body_data.get('content', body_data.get('text', ''))
                                elif isinstance(body_data, str):
                                    content = body_data
                            
                            # 2. من content
                            if not content:
                                content = thread.get('content', '')
                            
                            # 3. من fullContent  
                            if not content:
                                content = thread.get('fullContent', '')
                            
                            # 4. من summary
                            if not content:
                                content = thread.get('summary', '')
                            
                            if not content:
                                content = 'لا يوجد محتوى'
                            
                            # تنظيف HTML للحفاظ على التنسيق والـ Signature
                            import re
                            if '<' in content and '>' in content:
                                content = re.sub(r'<br\s*/?>', '\n', content, flags=re.IGNORECASE)
                                content = re.sub(r'<p[^>]*>', '', content, flags=re.IGNORECASE)
                                content = content.replace('</p>', '\n\n')
                                content = re.sub(r'<div[^>]*>', '\n', content, flags=re.IGNORECASE)
                                content = content.replace('</div>', '')
                                content = re.sub(r'<[^>]+>', '', content)
                                content = content.replace('&nbsp;', ' ')
                                content = content.replace('&amp;', '&')
                                content = content.replace('&lt;', '<')
                                content = content.replace('&gt;', '>')
                                content = content.replace('&quot;', '"')
                        except Exception as e:
                            self.logger.error(f"Error extracting content: {e}")
                            content = 'لا يوجد محتوى'
                        
                        direction_text = "وارد" if direction == 'in' else "صادر"
                        
                        f.write("="*80 + "\n")
                        f.write(f"المحادثة #{idx} - {direction_text} - {channel}\n\n")
                        f.write(f"From: {from_email}\n")
                        f.write(f"To: {to_list}\n")
                        if subject:
                            f.write(f"Subject: {subject}\n")
                        if created_time:
                            f.write(f"Time: {created_time}\n")
                        f.write("-"*80 + "\n")
                        f.write(f"\n{content}\n\n")
                
                messagebox.showinfo("نجاح", f"تم حفظ المحادثات في:\n{file_path}")
                self.logger.log(f"Saved threads to {file_path}")
                
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ في حفظ الملف:\n{str(e)}")
            self.logger.error(f"Error saving threads: {str(e)}")
    
    def export_threads_html(self, ticket_number, threads):
        """تصدير المحادثات كملف HTML قابل للعرض"""
        try:
            from tkinter import filedialog
            import html
            
            filename = f"threads_{ticket_number}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.html"
            
            file_path = filedialog.asksaveasfilename(
                defaultextension=".html",
                filetypes=[("HTML files", "*.html"), ("All files", "*.*")],
                initialfile=filename
            )
            
            if file_path:
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write("<!DOCTYPE html>\n")
                    f.write("<html dir='rtl'>\n<head>\n")
                    f.write("<meta charset='UTF-8'>\n")
                    f.write("<title>محادثات التذكرة #{}</title>\n".format(ticket_number))
                    f.write("<style>\n")
                    f.write("body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }\n")
                    f.write(".thread { background: white; margin: 20px 0; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }\n")
                    f.write(".header { font-size: 16px; font-weight: bold; color: #0066cc; margin-bottom: 10px; }\n")
                    f.write(".info { color: #006600; margin: 5px 0; }\n")
                    f.write(".content { margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #0066cc; }\n")
                    f.write("</style>\n")
                    f.write("</head>\n<body>\n")
                    f.write("<h1>محادثات التذكرة #{}</h1>\n".format(ticket_number))
                    
                    for idx, thread in enumerate(threads, 1):
                        # التحقق من نوع thread
                        if not isinstance(thread, dict):
                            continue
                        
                        # استخراج From
                        try:
                            from_email = 'غير محدد'
                            if 'fromEmailAddress' in thread:
                                from_data = thread.get('fromEmailAddress', {})
                                if isinstance(from_data, dict):
                                    from_email = from_data.get('emailAddress', from_data.get('email', 'غير محدد'))
                                elif isinstance(from_data, str):
                                    from_email = from_data
                        except:
                            from_email = 'غير محدد'
                        
                        # استخراج To
                        try:
                            to_list = 'غير محدد'
                            if 'toEmailAddressList' in thread:
                                to_emails = thread.get('toEmailAddressList', [])
                                if isinstance(to_emails, list):
                                    to_list = ', '.join([email.get('emailAddress', email.get('email', str(email))) if isinstance(email, dict) else str(email) for email in to_emails if email])
                        except:
                            to_list = 'غير محدد'
                        
                        direction = thread.get('direction', 'in')
                        channel = thread.get('channel', 'EMAIL')
                        created_time = thread.get('createdTime', '')
                        subject = thread.get('subject', '')
                        
                        # استخراج Content - للـ HTML export
                        try:
                            content = ''
                            # 1. من body
                            if 'body' in thread:
                                body_data = thread.get('body')
                                if isinstance(body_data, dict):
                                    content = body_data.get('content', body_data.get('text', ''))
                                elif isinstance(body_data, str):
                                    content = body_data
                            
                            # 2. من content
                            if not content:
                                content = thread.get('content', '')
                            
                            # 3. من fullContent
                            if not content:
                                content = thread.get('fullContent', '')
                            
                            # 4. من summary
                            if not content:
                                content = thread.get('summary', '')
                            
                            if not content:
                                content = 'لا يوجد محتوى'
                            
                            # تنظيف HTML للحفظ كـ HTML
                            import re
                            if '<' in content and '>' in content:
                                # تنظيف لكن نحتفظ ببعض التنسيق
                                content = re.sub(r'<br\s*/?>', '<br>', content, flags=re.IGNORECASE)
                                content = re.sub(r'<script[^>]*>.*?</script>', '', content, flags=re.DOTALL|re.IGNORECASE)
                                # استبدال entities
                                content = content.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
                                # ثم نعيد HTML الأساسي
                                content = content.replace('&lt;br&gt;', '<br>')
                                content = content.replace('&lt;br/&gt;', '<br>')
                                content = content.replace('&lt;br /&gt;', '<br>')
                        except Exception as e:
                            self.logger.error(f"Error extracting content for HTML: {e}")
                            content = 'لا يوجد محتوى'
                        
                        direction_text = "📥 وارد" if direction == 'in' else "📤 صادر"
                        
                        f.write("<div class='thread'>\n")
                        f.write(f"<div class='header'>المحادثة #{idx} - {direction_text} - {channel}</div>\n")
                        f.write(f"<div class='info'><strong>From:</strong> {from_email}</div>\n")
                        f.write(f"<div class='info'><strong>To:</strong> {to_list}</div>\n")
                        if subject:
                            f.write(f"<div class='info'><strong>Subject:</strong> {html.escape(subject)}</div>\n")
                        if created_time:
                            f.write(f"<div class='info'><strong>Time:</strong> {created_time}</div>\n")
                        f.write(f"<div class='content'>{html.escape(content)}</div>\n")
                        f.write("</div>\n")
                    
                    f.write("</body>\n</html>\n")
                
                messagebox.showinfo("نجاح", f"تم تصدير HTML في:\n{file_path}\n\nافتح الملف في المتصفح لعرض المحادثات.")
                self.logger.log(f"Exported HTML to {file_path}")
                
                # محاولة فتح الملف في المتصفح
                try:
                    import webbrowser
                    webbrowser.open(f"file://{os.path.abspath(file_path)}")
                except:
                    pass
                
        except Exception as e:
            messagebox.showerror("خطأ", f"حدث خطأ في تصدير HTML:\n{str(e)}")
            self.logger.error(f"Error exporting HTML: {str(e)}")

def main():
    root = tk.Tk()
    app = ZohoTicketsViewer(root)
    root.mainloop()

if __name__ == "__main__":
    main()

