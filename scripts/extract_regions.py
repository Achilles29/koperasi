#!/usr/bin/env python3
"""Extract administrative code/name hierarchy from Kepmendagri 300.2.2-2138/2025 annex.

Usage: python scripts/extract_regions.py /path/to/official-annex.pdf database/regions_2025.csv
The input PDF is available via the BPK/JDIH source cited in docs/roadmap.
"""
import csv
import re
import subprocess
import sys
from pathlib import Path


def parse(text):
    summary, detail = text.split('C.a.1) Kabupaten Aceh Selatan', 1)
    provinces = {}
    summary_pattern = re.compile(r'^\s*\d{1,2}\s+(\d{2})\s{2,}(.+?)\s{2,}\d+\s{2,}\d+\s{2,}\d+\s{2,}\d+\s{2,}[\d.]+')
    for line in summary.splitlines()[:130]:
        match = summary_pattern.match(line)
        if match:
            provinces[match.group(1)] = match.group(2).strip()

    regions = {code: ('province', name, '', '') for code, name in provinces.items()}
    code_pattern = re.compile(r'^\s*(\d{2}(?:\.\d{2}){0,2}(?:\.\d{4})?)\s{2,}(.+)$')
    for line in detail.splitlines():
        match = code_pattern.match(line)
        if not match:
            continue
        code, remainder = match.groups()
        if code in regions or code.count('.') == 0:
            continue
        depth = code.count('.')
        parts = re.split(r'\s{2,}', remainder.strip())
        if not parts:
            continue
        if depth in (2, 3):
            candidate = remainder.strip()
            candidate = re.sub(r'^\d+\s+', '', candidate)
            name = re.split(r'\s{2,}', candidate, maxsplit=1)[0].strip()
        else:
            name = re.sub(r'^\d+\s+', '', parts[0]).strip()
        if not name or name.isdigit():
            continue
        level = {1: 'regency', 2: 'district', 3: 'village'}[depth]
        parent_code = code.rsplit('.', 1)[0]
        village_type = ('kelurahan' if code.split('.')[-1].startswith('1') else 'desa') if depth == 3 else ''
        regions[code] = (level, name, parent_code, village_type)
    return regions


def main():
    if len(sys.argv) != 3:
        raise SystemExit(__doc__)
    pdf, output = Path(sys.argv[1]), Path(sys.argv[2])
    text = subprocess.run(['pdftotext', '-layout', str(pdf), '-'], capture_output=True, text=True, check=True).stdout
    regions = parse(text)
    counts = {level: sum(row[0] == level for row in regions.values()) for level in ('province', 'regency', 'district', 'village')}
    expected = {'province': 38, 'regency': 514, 'district': 7285, 'village': 83762}
    if counts != expected:
        raise SystemExit(f'Count mismatch; parsed={counts}, expected={expected}')
    missing = [code for code, (_, _, parent, _) in regions.items() if parent and parent not in regions]
    if missing:
        raise SystemExit(f'Missing parents: {missing[:12]}')
    output.parent.mkdir(parents=True, exist_ok=True)
    with output.open('w', newline='', encoding='utf-8') as file:
        writer = csv.writer(file)
        writer.writerow(['code', 'name', 'level', 'parent_code', 'village_type'])
        for code in sorted(regions, key=lambda c: (c.count('.'), c)):
            level, name, parent, village_type = regions[code]
            writer.writerow([code, name, level, parent, village_type])
    print(f'Exported {len(regions)} regions: {counts}; Rembang villages={sum(code.startswith("33.17.") and row[0] == "village" for code, row in regions.items())}')


if __name__ == '__main__':
    main()
