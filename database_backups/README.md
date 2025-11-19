# نسخ احتياطية قاعدة البيانات

هذا المجلد يحتوي على نسخ احتياطية من قاعدة البيانات.

## استعادة النسخة الاحتياطية

### MySQL/MariaDB:
```bash
gunzip database_backups/filename.sql.gz
mysql -u username -p database_name < database_backups/filename.sql
```

### PostgreSQL:
```bash
gunzip database_backups/filename.sql.gz
psql -U username -d database_name -f database_backups/filename.sql
```

**ملاحظة:** تأكد من قراءة ملف .env لمعرفة إعدادات قاعدة البيانات.
