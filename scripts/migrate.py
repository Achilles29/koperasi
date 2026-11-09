#!/usr/bin/env python3
"""Apply the ordered MariaDB schema files without printing database secrets."""
import re
import os
import pwd
import grp
from pathlib import Path
import pymysql

ROOT=Path(__file__).resolve().parents[1]
source=(ROOT/'application/config/koperasi_db.php').read_text()
def conf(key):
    match=re.search(r"'"+key+r"'\s*=>\s*'([^']*)'",source)
    if not match:raise RuntimeError('Missing database setting: '+key)
    return match.group(1)

def statements(path):
    sql='\n'.join(line for line in path.read_text().splitlines() if not line.lstrip().startswith('--'))
    return [part.strip() for part in sql.split(';') if part.strip()]

conn=pymysql.connect(host=conf('hostname'),user=conf('username'),password=conf('password'),database=conf('database'),charset='utf8mb4',autocommit=True)
try:
    with conn.cursor() as cur:
        cur.execute("SHOW TABLES LIKE 'schema_migrations'")
        has_migrations=cur.fetchone() is not None
        applied=set()
        if has_migrations:
            cur.execute('SELECT version FROM schema_migrations')
            applied={row[0] for row in cur.fetchall()}
        for version,filename in [('001_initial','schema.sql'),('002_assessment_rules','002_assessment_rules.sql'),('003_separate_form_scope','003_separate_form_scope.sql'),('004_assessment_access','004_assessment_access.sql'),('005_kdkmp_sites','005_kdkmp_sites.sql'),('006_kdkmp_progress','006_kdkmp_progress.sql'),('007_document_types','007_document_types.sql'),('008_public_content','008_public_content.sql'),('009_assessment_financial_inputs','009_assessment_financial_inputs.sql'),('010_public_complaint_access','010_public_complaint_access.sql'),('011_admin_verification','011_admin_verification.sql'),('012_profile','012_profile.sql'),('013_archive_permissions','013_archive_permissions.sql'),('014_draft_permissions','014_draft_permissions.sql'),('015_cooperative_unit_access','015_cooperative_unit_access.sql'),('016_annual_reports','016_annual_reports.sql'),('017_report_separation','017_report_separation.sql')]:
            if version in applied:
                print(version,'already applied')
                continue
            for statement in statements(ROOT/'database'/filename):
                if version=='002_assessment_rules' and statement.startswith('ALTER TABLE assessments'):
                    cur.execute("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assessments' AND COLUMN_NAME='target_unit_key'")
                    if cur.fetchone()[0]:continue
                cur.execute(statement)
            print(version,'applied')
finally:
    conn.close()

# Private report uploads must be writable by PHP and inaccessible from HTTP.
report_dir=ROOT/'private_uploads'/'reports'
report_dir.mkdir(parents=True,exist_ok=True)
if os.geteuid()==0:
    os.chown(report_dir,pwd.getpwnam('www').pw_uid,grp.getgrnam('www').gr_gid)
report_dir.chmod(0o750)
