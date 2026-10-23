from docx import Document
p=r"C:\Users\hp\Downloads\Rodriguez Flores Griselda Capítulo III Rev (2).docx"
d=Document(p)
for n in [25,43,61]:
 t=d.tables[n-1]
 print(f'--- TABLE {n} ---')
 for row in t.rows:
  print(' | '.join(c.text.replace('\n',' / ') for c in row.cells))
