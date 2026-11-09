#!/usr/bin/env python3
"""Seed numeric input cells from KKPKK Input sheets, omitting workbook example amounts."""
import hashlib,re
from pathlib import Path
import pymysql
from openpyxl import load_workbook

ROOT=Path(__file__).resolve().parents[1]
source=(ROOT/'application/config/koperasi_db.php').read_text()
def conf(key):return re.search(r"'"+key+r"'\s*=>\s*'([^']*)'",source).group(1)
routes=['ksp_12','ksp_34','kspps_12','kspps_34','nonksp_12','nonksp_34','nonksp_syariah_12','nonksp_syariah_34']
conn=pymysql.connect(host=conf('hostname'),user=conf('username'),password=conf('password'),database=conf('database'),charset='utf8mb4',autocommit=False)
try:
 with conn.cursor() as q:
  for route,path in zip(routes,sorted((ROOT/'docs/KKPKK').glob('[1-8].*.xlsx'))):
   q.execute("SELECT v.id,v.source_sha256 FROM assessment_form_versions v JOIN master_items m ON m.id=v.form_item_id WHERE m.code=%s AND v.version_code='211224'",(route,))
   found=q.fetchone()
   if not found or found[1]!=hashlib.sha256(path.read_bytes()).hexdigest():raise RuntimeError('Workbook version mismatch: '+path.name)
   version_id=found[0];sheet=load_workbook(path,read_only=True,data_only=False)['Input']
   start=next((r for r in range(1,sheet.max_row+1) if str(sheet.cell(r,2).value).strip().upper()=='NAMA REKENING'),None)
   if not start:raise RuntimeError('Financial section missing: '+path.name)
   fields=[]
   for row_no,row in enumerate(sheet.iter_rows(min_row=start+1,min_col=1,max_col=5),start+1):
    account,name,current,previous,guidance=[cell.value for cell in row]
    code=str(account).strip() if account is not None else ''
    if not (code.isdigit() and len(code)>=3 and name):continue
    for column,year_offset,cell in [('C',0,current),('D',-1,previous)]:
     if isinstance(cell,str) and cell.startswith('='):continue
     if cell is not None and not isinstance(cell,(int,float)) and str(cell).strip():raise RuntimeError(f'Unexpected input {path.name} {column}{row_no}')
     fields.append((version_id,f'Input-{column}{row_no}',code,str(name).strip(),year_offset,row_no,column,str(guidance or '').strip(),row_no*2+(column=='D')))
   for values in fields:
    q.execute('''INSERT INTO assessment_financial_fields(form_version_id,field_code,account_code,account_name,year_offset,source_row,source_column,guidance,sort_order)
       VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s)
       ON DUPLICATE KEY UPDATE account_code=VALUES(account_code),account_name=VALUES(account_name),year_offset=VALUES(year_offset),guidance=VALUES(guidance),sort_order=VALUES(sort_order)''',values)
   q.execute('SELECT COUNT(*) FROM assessment_financial_fields WHERE form_version_id=%s',(version_id,))
   if q.fetchone()[0]!=len(fields):raise RuntimeError('Field count mismatch: '+route)
   print(route,len(fields),'numeric cells; sample values omitted')
  conn.commit()
finally:conn.close()
