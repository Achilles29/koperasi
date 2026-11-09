#!/usr/bin/env python3
"""Import eight workbook question sets as REVIEW versions; no official score engine."""
import hashlib,json,re
from pathlib import Path
import pymysql
from openpyxl import load_workbook
ROOT=Path(__file__).resolve().parents[1]
text=(ROOT/'application/config/koperasi_db.php').read_text()
def cfg(key):return re.search(r"'"+key+r"'\s*=>\s*'([^']*)'",text).group(1)
ROUTES=['ksp_12','ksp_34','kspps_12','kspps_34','nonksp_12','nonksp_34','nonksp_syariah_12','nonksp_syariah_34']
conn=pymysql.connect(host=cfg('hostname'),user=cfg('username'),password=cfg('password'),database=cfg('database'),charset='utf8mb4',autocommit=False)
try:
 with conn.cursor() as q:
  q.execute("SELECT m.code,m.id,m.name FROM master_items m JOIN master_definitions d ON d.id=m.definition_id WHERE d.slug='paket-kkpkk'")
  items={code:(iid,name) for code,iid,name in q.fetchall()}
  for route,path in zip(ROUTES,sorted((ROOT/'docs/KKPKK').glob('[1-8].*.xlsx'))):
   if route not in items:raise RuntimeError('Missing master: '+route)
   digest=hashlib.sha256(path.read_bytes()).hexdigest();form_id,title=items[route]
   q.execute('SELECT id,source_sha256 FROM assessment_form_versions WHERE form_item_id=%s AND version_code=%s',(form_id,'211224'))
   prior=q.fetchone()
   if prior and prior[1]!=digest:raise RuntimeError('Workbook changed; create a new version before import: '+path.name)
   if prior:version_id=prior[0]
   else:
    q.execute('INSERT INTO assessment_form_versions(form_item_id,version_code,title,source_file_name,source_sha256,status) VALUES(%s,%s,%s,%s,%s,%s)',(form_id,'211224',title,path.name,digest,'review'))
    version_id=q.lastrowid
   sheet=load_workbook(path,read_only=True,data_only=False)['Input']
   section='';subsection='';questions=[]
   for cells in sheet.iter_rows(min_col=1,max_col=5):
    a,b,c,d,e=[cell.value for cell in cells];row=cells[0].row
    if str(b).strip().upper()=='NAMA REKENING':break
    if b and c in ('Terpenuhi','Tidak Terpenuhi'):
     if not isinstance(d,str) or not re.fullmatch(r'=IF\(C'+str(row)+r'="Terpenuhi",1,0\)',d,re.I):
      raise RuntimeError(f'Unexpected formula {path.name} Input!D{row}: {d}')
     questions.append((str(row),str(b).strip(),str(e or '').strip(),section,subsection,d,row))
    elif b and (c is None) and a is not None:
     marker=str(a).strip()
     if marker in 'ABCDE' and len(marker)==1:section=str(b).strip()
     elif marker in ('I','II','III','IV','V','VI','VII','VIII','IX','X'):subsection=str(b).strip()
   for code,prompt,guidance,sect,subsect,formula,row in questions:
    meta=json.dumps({'section':sect,'subsection':subsect,'source_sheet':'Input','source_row':row},ensure_ascii=False)
    scoring=json.dumps({'source_formula':formula,'binary_rule':'Terpenuhi=1; Tidak Terpenuhi=0','official_score_ready':False},ensure_ascii=False)
    q.execute('''INSERT INTO assessment_indicators(form_version_id,indicator_code,prompt,answer_type,is_required,condition_json,scoring_json,guidance,sort_order)
      VALUES(%s,%s,%s,'boolean',1,%s,%s,%s,%s)
      ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),condition_json=VALUES(condition_json),scoring_json=VALUES(scoring_json),guidance=VALUES(guidance),sort_order=VALUES(sort_order)''',
      (version_id,code,prompt,meta,scoring,guidance,row))
   q.execute('SELECT COUNT(*) FROM assessment_indicators WHERE form_version_id=%s',(version_id,));count=q.fetchone()[0]
   if count!=len(questions):raise RuntimeError('Question count mismatch')
   print(route,len(questions),path.name)
  conn.commit()
finally:conn.close()
