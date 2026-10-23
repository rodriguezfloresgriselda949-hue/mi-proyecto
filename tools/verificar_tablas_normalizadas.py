from zipfile import ZipFile
from lxml import etree
from pathlib import Path
p=Path(r"documentos\Capitulo_III_Tablas_Normalizadas.docx")
W="http://schemas.openxmlformats.org/wordprocessingml/2006/main"; ns={"w":W}
with ZipFile(p) as z:
    root=etree.fromstring(z.read("word/document.xml"))
body=root.find('.//w:body',ns)
children=list(body)
tables=[i for i,x in enumerate(children) if x.tag==f"{{{W}}}tbl"]
print('Tablas:',len(tables))
missing=[]
for n,pos in enumerate(tables,1):
    before=children[pos-2:pos]
    after=children[pos+1] if pos+1 < len(children) else None
    texts=[''.join(e.xpath('.//w:t/text()',namespaces=ns)).strip() for e in before]
    note=''.join(after.xpath('.//w:t/text()',namespaces=ns)).strip() if after is not None else ''
    if not (texts[0]==f'Tabla {n}' and texts[1] and note.startswith('Nota.')): missing.append(n)
print('Tablas sin formato completo:',missing)
print('Diagramas conservados:', len(root.xpath('.//w:drawing',namespaces=ns)))
print('Imagenes integradas:', len(root.xpath('.//w:drawing|.//w:pict',namespaces=ns)))
