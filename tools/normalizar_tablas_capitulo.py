from copy import deepcopy
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re
from lxml import etree

SOURCE = Path(r"C:\Users\hp\Downloads\Rodriguez Flores Griselda Capítulo III Rev (2).docx")
OUTPUT = Path(r"C:\laragon\www\proyecto.7\documentos\Capitulo_III_Tablas_Normalizadas.docx")
W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W}

def q(name):
    return f"{{{W}}}{name}"

historias = [
    "Iniciar sesión y acceder según el rol", "Gestionar usuarios del sistema", "Administrar perfil",
    "Consultar panel administrativo y calendario de reservas", "Gestionar agenda de reservas",
    "Gestionar clientes", "Consultar historial de clientes", "Gestionar servicios de belleza",
    "Gestionar reservas", "Gestionar productos e inventario", "Administrar categoría de productos",
    "Consultar catálogo público de productos para el cliente", "Solicitar reserva de productos desde el catálogo",
    "Gestionar solicitudes de reserva de productos", "Gestionar proveedores", "Registrar compras de productos",
    "Gestionar ventas", "Registrar adelantos y pagos al personal", "Generar reportes de ventas e inventario",
    "Generar reportes de servicios", "Reporte de servicios por empleado",
]

titulos = [
    "Roles y responsabilidades del equipo Scrum",
    "Tipos de usuarios del sistema",
] + [f"Historia de usuario - {historia}" for historia in historias] + [
    "Product Backlog - Pila del Producto",
    "Planificación del primer Sprint",
] + [f"Especificaciones de caso de uso para la HU{n}" for n in range(1, 8)] + [
    "Tareas por cada historia de usuario del primer Sprint",
    "Ejecución de tareas del primer Sprint",
] + [f"Revisión de los criterios de aceptación de la HU{n}" for n in range(1, 8)] + [
    "Visión retrospectiva del primer Sprint",
    "Planificación del segundo Sprint",
] + [f"Especificaciones de caso de uso para la HU{n}" for n in range(8, 15)] + [
    "Tareas por cada historia de usuario del segundo Sprint",
    "Ejecución de tareas del segundo Sprint",
] + [f"Revisión de los criterios de aceptación de la HU{n}" for n in range(8, 15)] + [
    "Visión retrospectiva del segundo Sprint",
    "Planificación del tercer Sprint",
] + [f"Especificaciones de caso de uso para la HU{n}" for n in range(15, 22)] + [
    "Tareas por cada historia de usuario del tercer Sprint",
    "Ejecución de tareas del tercer Sprint",
] + [f"Revisión de los criterios de aceptación de la HU{n}" for n in range(15, 22)] + [
    "Visión retrospectiva del tercer Sprint",
]

notas = [
    "La tabla presenta los roles Scrum y las responsabilidades de los integrantes que participan en el desarrollo del sistema.",
    "La tabla presenta los tipos de usuarios del sistema y las funciones que pueden realizar de acuerdo con su nivel de acceso.",
] + [f"La tabla presenta la historia de usuario correspondiente a la funcionalidad de {historia.lower()}." for historia in historias] + [
    "La tabla presenta las historias de usuario del sistema, su descripción y el Sprint asignado para su desarrollo.",
    "La tabla presenta las historias de usuario, el tiempo estimado y las fechas establecidas para el primer Sprint.",
] + [f"La tabla presenta la especificación del caso de uso de la HU{n}, sus actores y criterios de aceptación." for n in range(1, 8)] + [
    "La tabla presenta las tareas planificadas para las historias de usuario del primer Sprint.",
    "La tabla presenta el avance de las tareas planificadas durante la ejecución del primer Sprint.",
] + [f"La tabla presenta la revisión de los criterios de aceptación definidos para la HU{n}." for n in range(1, 8)] + [
    "La tabla presenta los aciertos, errores y recomendaciones de mejora identificados en el primer Sprint.",
    "La tabla presenta las historias de usuario, el tiempo estimado y las fechas establecidas para el segundo Sprint.",
] + [f"La tabla presenta la especificación del caso de uso de la HU{n}, sus actores y criterios de aceptación." for n in range(8, 15)] + [
    "La tabla presenta las tareas planificadas para las historias de usuario del segundo Sprint.",
    "La tabla presenta el avance de las tareas planificadas durante la ejecución del segundo Sprint.",
] + [f"La tabla presenta la revisión de los criterios de aceptación definidos para la HU{n}." for n in range(8, 15)] + [
    "La tabla presenta los aciertos, errores y recomendaciones de mejora identificados en el segundo Sprint.",
    "La tabla presenta las historias de usuario, el tiempo estimado y las fechas establecidas para el tercer Sprint.",
] + [f"La tabla presenta la especificación del caso de uso de la HU{n}, sus actores y criterios de aceptación." for n in range(15, 22)] + [
    "La tabla presenta las tareas planificadas para las historias de usuario del tercer Sprint.",
    "La tabla presenta el avance de las tareas planificadas durante la ejecución del tercer Sprint.",
] + [f"La tabla presenta la revisión de los criterios de aceptación definidos para la HU{n}." for n in range(15, 22)] + [
    "La tabla presenta los aciertos, errores y recomendaciones de mejora identificados en el tercer Sprint.",
]

assert len(titulos) == 78, len(titulos)
assert len(notas) == 78, len(notas)

def p_text(p):
    return ''.join(p.xpath('.//w:t/text()', namespaces=NS)).strip()

def new_paragraph(parts, italic=False):
    p = etree.Element(q('p'))
    ppr = etree.SubElement(p, q('pPr'))
    spacing = etree.SubElement(ppr, q('spacing'))
    spacing.set(q('before'), '80')
    spacing.set(q('after'), '60')
    for value, bold in parts:
        run = etree.SubElement(p, q('r'))
        rpr = etree.SubElement(run, q('rPr'))
        if bold:
            etree.SubElement(rpr, q('b'))
        if italic:
            etree.SubElement(rpr, q('i'))
        font = etree.SubElement(rpr, q('rFonts'))
        font.set(q('ascii'), 'Arial')
        font.set(q('hAnsi'), 'Arial')
        size = etree.SubElement(rpr, q('sz'))
        size.set(q('val'), '20')
        t = etree.SubElement(run, q('t'))
        t.text = value
    return p

def is_paragraph(el):
    return el.tag == q('p')

def removable_title(text):
    if not text:
        return False
    return bool(re.search(r'(Historia de Usuario|Especificaciones de caso|Product Backlog|Planificación del|Tareas por cada|Revisión de los criterios|Visión retrospectiva|Roles y responsabilidades|Tipos de usuarios)', text, re.I))

def is_table_caption(text):
    return bool(re.match(r'^\s*Tabla\s*\d+', text, re.I))

with ZipFile(SOURCE, 'r') as zin:
    document = etree.fromstring(zin.read('word/document.xml'))
    body = document.find('.//w:body', NS)
    tables = [el for el in list(body) if el.tag == q('tbl')]
    if len(tables) != 78:
        raise RuntimeError(f'Se esperaban 78 tablas y se encontraron {len(tables)}')

    for idx, table in enumerate(tables):
        parent = table.getparent()
        # Remove the prior caption/title only when they immediately belong to this table.
        previous = table.getprevious()
        if previous is not None and is_paragraph(previous):
            previous_text = p_text(previous)
            before_previous = previous.getprevious()
            if is_table_caption(previous_text):
                parent.remove(previous)
            elif before_previous is not None and is_paragraph(before_previous) and is_table_caption(p_text(before_previous)) and removable_title(previous_text):
                parent.remove(before_previous)
                parent.remove(previous)

        caption = new_paragraph([(f'Tabla {idx + 1}', True)])
        title = new_paragraph([(titulos[idx], True)], italic=False)
        table.addprevious(caption)
        table.addprevious(title)

        # Update the existing nearest note when it directly follows the table; otherwise add one.
        following = table.getnext()
        note = new_paragraph([('Nota. ', True), (notas[idx], False)])
        if following is not None and is_paragraph(following) and re.match(r'^\s*\.?\s*Nota\.', p_text(following), re.I):
            parent.replace(following, note)
        else:
            table.addnext(note)

    rebuilt = etree.tostring(document, xml_declaration=True, encoding='UTF-8', standalone=True)
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    with ZipFile(OUTPUT, 'w', ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            data = rebuilt if item.filename == 'word/document.xml' else zin.read(item.filename)
            zout.writestr(item, data)

print(OUTPUT)
