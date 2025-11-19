Set objPPT = CreateObject("PowerPoint.Application")
objPPT.Visible = True

Set objPresentation = objPPT.Presentations.Add

' Slide 1: نظرة عامة | Overview
Set objSlide1 = objPresentation.Slides.Add(1, 1)
objSlide1.Shapes.Title.TextFrame.TextRange.Text = "نظام إدارة الموظفين | Employee Management System"
objSlide1.Shapes.AddTextbox(1, 50, 100, 600, 400).TextFrame.TextRange.Text = "🎯 نظرة عامة | Overview" & vbCrLf & vbCrLf & _
"نظام إدارة الموظفين هو جزء أساسي من المنصة ويسمح لك بإدارة جميع الموظفين، ملفاتهم الشخصية، صلاحياتهم، وأدوارهم داخل الشركة." & vbCrLf & vbCrLf & _
"The Employee Management System is a core module that enables you to manage employees, their profiles, roles, and permissions within the organization." & vbCrLf & vbCrLf & _
"🔗 الوصول للنظام: http://localhost:8000/login"

' Slide 2: الأدوار المتاحة | Available Roles
Set objSlide2 = objPresentation.Slides.Add(2, 1)
objSlide2.Shapes.Title.TextFrame.TextRange.Text = "الأدوار المتاحة | Available Roles"
objSlide2.Shapes.AddTextbox(1, 50, 100, 600, 400).TextFrame.TextRange.Text = "👥 الأدوار المتاحة | Available Roles" & vbCrLf & vbCrLf & _
"🔧 مطور برمجيات | Software Developer – صلاحيات كاملة" & vbCrLf & _
"👑 الرئيس التنفيذي (CEO) | Chief Executive Officer – Full Access" & vbCrLf & _
"👨‍💼 المدير الرئيسي | Main Manager – Team & Task Management" & vbCrLf & _
"👨‍💻 قائد الفريق | Team Leader – Limited Team Management" & vbCrLf & _
"👤 الموظف | Employee – Personal Profile & Team Info" & vbCrLf & vbCrLf & _
"🔐 نظام الصلاحيات:" & vbCrLf & _
"• users.view - عرض الموظفين" & vbCrLf & _
"• users.create - إضافة موظف" & vbCrLf & _
"• users.edit - تعديل موظف" & vbCrLf & _
"• users.delete - حذف موظف" & vbCrLf & _
"• users.manage_team - إدارة الفريق"

' Slide 3: الصفحات الرئيسية | Main Pages
Set objSlide3 = objPresentation.Slides.Add(3, 1)
objSlide3.Shapes.Title.TextFrame.TextRange.Text = "الصفحات الرئيسية | Main Pages"
objSlide3.Shapes.AddTextbox(1, 50, 100, 600, 400).TextFrame.TextRange.Text = "🚀 الصفحات الرئيسية | Main Pages" & vbCrLf & vbCrLf & _
"📋 قائمة الموظفين | Employee List → /users" & vbCrLf & _
"   • بحث متقدم | Advanced Search" & vbCrLf & _
"   • تصفية حسب القسم والدور | Filter by Department & Role" & vbCrLf & _
"   • عرض متعدد الصفحات | Pagination" & vbCrLf & vbCrLf & _
"📊 لوحة التحكم | Dashboard → /dashboard" & vbCrLf & _
"   • إحصائيات الموظفين | Employee Statistics" & vbCrLf & _
"   • المهام الحديثة | Recent Tasks" & vbCrLf & vbCrLf & _
"👤 الملف الشخصي | Profile → /profile" & vbCrLf & _
"   • بيانات شخصية | Personal Information" & vbCrLf & _
"   • إعدادات الخصوصية | Privacy Settings"

' Slide 4: إدارة الموظفين | Employee Management
Set objSlide4 = objPresentation.Slides.Add(4, 1)
objSlide4.Shapes.Title.TextFrame.TextRange.Text = "إدارة الموظفين | Employee Management"
objSlide4.Shapes.AddTextbox(1, 50, 100, 600, 400).TextFrame.TextRange.Text = "👥 إدارة الموظفين | Employee Management" & vbCrLf & vbCrLf & _
"➕ إضافة موظف جديد | Add New Employee (/users/create)" & vbCrLf & _
"   • الاسم والبريد الإلكتروني | Name & Email" & vbCrLf & _
"   • كلمة المرور والدور | Password & Role" & vbCrLf & _
"   • معلومات إضافية | Additional Information" & vbCrLf & vbCrLf & _
"✏️ تعديل موظف | Edit Employee (/users/{id}/edit)" & vbCrLf & _
"   • تحديث البيانات الشخصية | Update Personal Data" & vbCrLf & _
"   • تغيير الدور والصلاحيات | Change Role & Permissions" & vbCrLf & vbCrLf & _
"📄 عرض تفاصيل الموظف | View Employee Details (/users/{id})" & vbCrLf & _
"   • بيانات شخصية وعملية | Personal & Work Info" & vbCrLf & _
"   • المهام والطلبات | Tasks & Requests"

' Slide 5: الميزات المتقدمة | Advanced Features
Set objSlide5 = objPresentation.Slides.Add(5, 1)
objSlide5.Shapes.Title.TextFrame.TextRange.Text = "الميزات المتقدمة | Advanced Features"
objSlide5.Shapes.AddTextbox(1, 50, 100, 600, 400).TextFrame.TextRange.Text = "🔧 الميزات المتقدمة | Advanced Features" & vbCrLf & vbCrLf & _
"📊 التحديث الجماعي | Batch Update (/users/batch-edit)" & vbCrLf & _
"   • تحديث بيانات عدة موظفين | Bulk Edit Employees" & vbCrLf & _
"   • تغيير القسم | Change Department" & vbCrLf & _
"   • تعيين مدير جديد | Assign New Manager" & vbCrLf & vbCrLf & _
"📇 بطاقة الاتصال | Contact Card (/users/{id}/contact-card)" & vbCrLf & _
"   • عرض جميع بيانات الموظف | Full Employee Data" & vbCrLf & _
"   • زملاء العمل | Colleagues" & vbCrLf & _
"   • مهام مشتركة | Shared Tasks" & vbCrLf & _
"   • وظائف تواصل سريع | Quick Actions" & vbCrLf & vbCrLf & _
"🌐 دعم لغتين | Multi-language Support (Arabic + English)" & vbCrLf & _
"📱 تصميم متجاوب | Responsive Design"

' Add images to slides if they exist
Dim imagePath
imagePath = "screenshots\"

' Try to add images to slides
On Error Resume Next

' Add image to slide 1 if exists
If objFSO.FileExists(imagePath & "login_page.png") Then
    objSlide1.Shapes.AddPicture(imagePath & "login_page.png", False, True, 650, 100, 200, 150)
End If

' Add image to slide 2 if exists
If objFSO.FileExists(imagePath & "dashboard.png") Then
    objSlide2.Shapes.AddPicture(imagePath & "dashboard.png", False, True, 650, 100, 200, 150)
End If

' Add image to slide 3 if exists
If objFSO.FileExists(imagePath & "users_management.png") Then
    objSlide3.Shapes.AddPicture(imagePath & "users_management.png", False, True, 650, 100, 200, 150)
End If

' Add image to slide 4 if exists
If objFSO.FileExists(imagePath & "contact_card.png") Then
    objSlide4.Shapes.AddPicture(imagePath & "contact_card.png", False, True, 650, 100, 200, 150)
End If

' Add image to slide 5 if exists
If objFSO.FileExists(imagePath & "tasks_management.png") Then
    objSlide5.Shapes.AddPicture(imagePath & "tasks_management.png", False, True, 650, 100, 200, 150)
End If

On Error GoTo 0

' Save the presentation
objPresentation.SaveAs "Employee_Management_System_Presentation.pptx"

WScript.Echo "تم إنشاء العرض التقديمي بنجاح! | Presentation created successfully!"
WScript.Echo "تم حفظ الملف كـ: Employee_Management_System_Presentation.pptx"
