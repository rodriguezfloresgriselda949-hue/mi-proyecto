from docx import Document
from docx.table import Table
from docx.text.paragraph import Paragraph
from docx.oxml.table import CT_Tbl
from docx.oxml.text.paragraph import CT_P

path = r"C:\Users\hp\Downloads\Rodriguez Flores Griselda Capítulo III Rev (2).docx"
doc = Document(path)
body = doc.element.body
items=[]
for child in body.iterchildren():
    if isinstance(child, CT_P): items.append(('p', Paragraph(child, doc)))
    elif isinstance(child, CT_Tbl): items.append(('t', Table(child, doc)))
for i,(kind,obj) in enumerate(items):
    if kind=='t':
        before=[]; after=[]
        for j in range(max(0,i-5),i):
            if items[j][0]=='p': before.append(items[j][1].text.strip())
        for j in range(i+1,min(len(items),i+6)):
            if items[j][0]=='p': after.append(items[j][1].text.strip())
        rows=len(obj.rows); cols=len(obj.columns) if rows else 0
        cells=[' | '.join(cell.text.replace('\n',' / ')[:80] for cell in row.cells[:min(cols,5)]) for row in obj.rows[:2]]
        print(f"\nTABLE {sum(1 for x in items[:i+1] if x[0]=='t')}  {rows}x{cols}")
        print('BEFORE:', ' || '.join(x for x in before if x))
        print('CELLS:', ' ### '.join(cells))
        print('AFTER:', ' || '.join(x for x in after if x))
