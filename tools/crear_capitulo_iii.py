from pathlib import Path
from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.enum.section import WD_SECTION
from docx.shared import Inches, Pt, RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

OUT = Path(__file__).resolve().parents[1] / 'documentos' / 'Capitulo_III_Mejorado_Miss_Unas_Valentina.docx'


def shade(cell, color):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:fill'), color)
    tc_pr.append(shd)


def border(cell):
    tc_pr = cell._tc.get_or_add_tcPr()
    borders = OxmlElement('w:tcBorders')
    for side in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
        tag = OxmlElement(f'w:{side}')
        tag.set(qn('w:val'), 'single')
        tag.set(qn('w:sz'), '4')
        tag.set(qn('w:color'), 'D9D9D9')
        borders.append(tag)
    tc_pr.append(borders)


def set_cell(cell, text, bold=False, color=None, size=9):
    cell.text = ''
    p = cell.paragraphs[0]
    p.paragraph_format.space_after = Pt(0)
    p.paragraph_format.space_before = Pt(0)
    r = p.add_run(str(text))
    r.bold = bold
    r.font.name = 'Arial'
    r._element.rPr.rFonts.set(qn('w:ascii'), 'Arial')
    r._element.rPr.rFonts.set(qn('w:hAnsi'), 'Arial')
    r.font.size = Pt(size)
    if color:
        r.font.color.rgb = RGBColor.from_string(color)
    cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
    border(cell)


def add_table(doc, headers, rows, widths=None):
    table = doc.add_table(rows=1, cols=len(headers))
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.style = 'Table Grid'
    table.autofit = False
    for idx, title in enumerate(headers):
        cell = table.rows[0].cells[idx]
        set_cell(cell, title, True, 'FFFFFF', 9)
        shade(cell, '7A1F3D')
        if widths:
            cell.width = Inches(widths[idx])
    for row_idx, row in enumerate(rows):
        cells = table.add_row().cells
        for idx, value in enumerate(row):
            set_cell(cells[idx], value, size=8.5)
            if row_idx % 2:
                shade(cells[idx], 'F8EEF2')
            if widths:
                cells[idx].width = Inches(widths[idx])
    doc.add_paragraph().paragraph_format.space_after = Pt(2)
    return table


def heading(doc, text, level=1):
    p = doc.add_paragraph(style=f'Heading {level}')
    p.paragraph_format.space_before = Pt(12 if level == 1 else 8)
    p.paragraph_format.space_after = Pt(5)
    r = p.add_run(text)
    r.font.name = 'Arial'
    r._element.rPr.rFonts.set(qn('w:ascii'), 'Arial')
    r._element.rPr.rFonts.set(qn('w:hAnsi'), 'Arial')
    r.font.color.rgb = RGBColor(0, 0, 0)


def para(doc, text):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    p.paragraph_format.first_line_indent = Inches(.28)
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    r = p.add_run(text)
    r.font.name = 'Arial'
    r._element.rPr.rFonts.set(qn('w:ascii'), 'Arial')
    r._element.rPr.rFonts.set(qn('w:hAnsi'), 'Arial')
    r.font.size = Pt(10.5)


doc = Document()
sec = doc.sections[0]
sec.top_margin = Inches(.8); sec.bottom_margin = Inches(.75)
sec.left_margin = Inches(.8); sec.right_margin = Inches(.8)
styles = doc.styles
styles['Normal'].font.name = 'Arial'; styles['Normal'].font.size = Pt(10.5)

p = doc.add_paragraph(style='Title')
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = p.add_run('Capitulo III Desarrollo del Sistema de Informacion Web')
r.font.name = 'Arial'; r.font.size = Pt(18); r.font.bold = True; r.font.color.rgb = RGBColor(0, 0, 0)
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = p.add_run('Sistema web para la gestion de reservas compra y venta del Salon de Belleza Mis Uñas Valentina')
r.italic = True; r.font.name = 'Arial'; r.font.size = Pt(11)

heading(doc, '3 1 Descripcion del dominio del problema')
para(doc, 'El Salon de Belleza Mis Uñas Valentina brinda servicios de estetica y cuidado personal, principalmente manicure, pedicure, maquillaje y peinados. De manera complementaria comercializa productos cosmeticos, perfumes e insumos relacionados. La atencion se realiza en el establecimiento y requiere coordinar clientes, horarios, servicios, personal, productos y cobros.')
para(doc, 'Actualmente, una parte importante de la informacion se registra de forma manual. Las reservas se anotan en un cuaderno y el control de compras, ventas y existencias depende de revisiones periodicas. Esta modalidad dificulta consultar la agenda, identificar reservas duplicadas, conocer el stock disponible y obtener reportes oportunos para la toma de decisiones.')
para(doc, 'El sistema propuesto centraliza la informacion del salon. Permitira registrar clientes, programar reservas, administrar servicios, controlar el inventario, registrar compras y ventas, y generar reportes. Con ello se busca reducir errores de registro, mejorar la atencion al cliente y disponer de informacion confiable sobre ingresos, productos de baja existencia y servicios con mayor demanda.')

heading(doc, '3 2 Roles y partes interesadas')
add_table(doc, ['Rol o actor', 'Responsabilidad principal'], [
    ['Propietaria y Product Owner', 'Define las necesidades del salon, prioriza las funcionalidades y valida los resultados entregados.'],
    ['Scrum Master', 'Facilita las actividades Scrum, identifica impedimentos y promueve la mejora continua del equipo.'],
    ['Desarrollador', 'Analiza, programa, prueba e integra las funcionalidades comprometidas en cada Sprint.'],
    ['Administrador', 'Gestiona usuarios, productos, servicios, proveedores, compras, ventas y reportes administrativos.'],
    ['Personal', 'Registra clientes, reservas y ventas; consulta servicios, disponibilidad y productos para atender al cliente.'],
    ['Cliente', 'Solicita servicios o productos y proporciona los datos necesarios para su atencion.'],
], [1.65, 5.6])
para(doc, 'Nota. Los roles Scrum corresponden al equipo de desarrollo; los roles Administrador, Personal y Cliente corresponden a quienes interactuan con el sistema.')

heading(doc, '3 3 Historias de usuario')
para(doc, 'Las historias de usuario expresan las necesidades funcionales del sistema desde la perspectiva de cada rol. La redaccion mantiene la estructura Como quiero para y la numeracion es consecutiva para facilitar su trazabilidad durante los Sprints.')
historias = [
('HU1', 'Administrador y personal', 'Iniciar sesion con usuario y contraseña.', 'Acceder unicamente a las funciones autorizadas por su rol.'),
('HU2', 'Administrador', 'Registrar, modificar, eliminar y listar usuarios.', 'Controlar el acceso y los permisos del sistema.'),
('HU3', 'Usuario interno', 'Visualizar y actualizar mis datos y contraseña.', 'Mantener mi cuenta vigente y protegida.'),
('HU4', 'Personal', 'Registrar, buscar, modificar y listar clientes.', 'Mantener organizada la informacion para su atencion.'),
('HU5', 'Administrador', 'Gestionar los servicios de belleza, precios y duracion.', 'Mantener actualizada la oferta disponible.'),
('HU6', 'Personal', 'Registrar reservas con cliente, fecha, hora y servicio.', 'Organizar la atencion y evitar conflictos de horario.'),
('HU7', 'Personal', 'Modificar o cancelar reservas registradas.', 'Mantener actualizada la agenda del salon.'),
('HU8', 'Personal', 'Visualizar la agenda de citas por fecha y horario.', 'Planificar la atencion diaria.'),
('HU9', 'Administrador', 'Registrar, modificar, eliminar y listar productos.', 'Controlar los articulos disponibles para la venta.'),
('HU10', 'Administrador', 'Registrar y actualizar proveedores.', 'Organizar la informacion de abastecimiento.'),
('HU11', 'Administrador', 'Registrar compras de productos e insumos.', 'Actualizar existencias y conocer gastos de abastecimiento.'),
('HU12', 'Personal', 'Registrar ventas de productos y servicios.', 'Controlar las transacciones e ingresos del salon.'),
('HU13', 'Personal', 'Consultar el historial de servicios y compras de un cliente.', 'Brindar una atencion personalizada.'),
('HU14', 'Administrador', 'Generar reportes de ventas por periodo.', 'Analizar el comportamiento economico del salon.'),
('HU15', 'Administrador', 'Generar reportes de inventario y bajo stock.', 'Detectar faltantes y planificar reposiciones.'),
('HU16', 'Personal', 'Recibir alertas de proximas citas.', 'Mejorar la puntualidad y organizacion de la atencion.'),
('HU17', 'Personal', 'Consultar horarios disponibles.', 'Programar reservas sin duplicidad.'),
('HU18', 'Administrador', 'Registrar y administrar categorias de productos.', 'Clasificar correctamente el inventario.'),
('HU19', 'Administrador', 'Registrar marcas de productos.', 'Identificar los productos comercializados.'),
('HU20', 'Personal', 'Consultar los productos disponibles y su stock.', 'Informar correctamente a los clientes.'),
('HU21', 'Administrador', 'Registrar promociones y descuentos.', 'Incrementar ventas y atraer clientes.'),
('HU22', 'Administrador', 'Visualizar el historial de ventas.', 'Dar seguimiento a las transacciones realizadas.'),
('HU23', 'Administrador', 'Corregir ventas registradas con errores.', 'Mantener informacion de ventas correcta y actualizada.'),
('HU24', 'Administrador', 'Consultar estadisticas de los servicios mas solicitados.', 'Tomar decisiones sobre promociones y mejoras.'),
('HU25', 'Administrador', 'Registrar porcentajes de comision por servicios atendidos.', 'Calcular el pago correspondiente a cada empleado.'),
('HU26', 'Administrador', 'Consultar el historial de comisiones por periodo.', 'Verificar y mantener ordenados los pagos del personal.'),
('HU27', 'Administrador y personal', 'Consultar el inventario de productos.', 'Conocer el stock y los precios durante la atencion al cliente.'),
]
add_table(doc, ['Codigo', 'Como', 'Quiero', 'Para'], historias, [.55, 1.05, 2.8, 2.85])
para(doc, 'Nota. HU21 reemplaza la numeracion duplicada de promociones; las historias posteriores se renumeraron de forma consecutiva. HU27 formaliza el acceso de consulta al inventario para administrador y personal.')

heading(doc, '3 4 Matriz de permisos del sistema')
para(doc, 'Los permisos se asignan por rol. El administrador tiene control completo sobre los procesos administrativos. El personal puede realizar las operaciones necesarias para la atencion diaria, sin modificar informacion sensible de configuracion o inventario.')
add_table(doc, ['Funcion', 'Administrador', 'Personal'], [
['Usuarios', 'Gestion completa', 'Sin acceso'],
['Clientes', 'Gestion completa', 'Gestion completa'],
['Servicios', 'Gestion completa', 'Consulta'],
['Reservas y agenda', 'Gestion completa', 'Gestion de sus reservas'],
['Productos e inventario', 'Gestion completa', 'Consulta de productos y stock'],
['Proveedores y compras', 'Gestion completa', 'Sin acceso'],
['Ventas', 'Consulta y anulacion', 'Registro y consulta de sus ventas'],
['Reportes y comisiones', 'Consulta y generacion', 'Sin acceso'],
], [2.2, 2.3, 2.75])

heading(doc, '3 5 Criterios de aceptacion para el acceso a productos')
para(doc, 'La funcion Consultar productos e inventario se considera terminada cuando se cumplen los siguientes criterios:')
for item in [
    'Un usuario con rol Administrador puede abrir Productos y visualizar el listado de inventario.',
    'Un usuario con rol Personal puede abrir Productos y visualizar el listado de inventario sin recibir el mensaje de acceso denegado.',
    'El personal puede buscar productos y consultar descripcion, categoria, marca, precio de venta y stock.',
    'Solo el Administrador visualiza y puede usar las opciones Nuevo producto, Editar y Eliminar.',
    'Un usuario no autenticado o con rol Cliente no puede acceder al panel interno de productos.',
]:
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_after = Pt(4)
    r = p.add_run(item); r.font.name = 'Arial'; r.font.size = Pt(10.5)

heading(doc, '3 6 Priorizacion inicial del Product Backlog')
add_table(doc, ['Prioridad', 'Historias', 'Justificacion'], [
['Alta', 'HU1, HU4, HU5, HU6, HU7, HU8, HU12, HU20, HU27', 'Permiten iniciar sesion, atender clientes, registrar reservas y realizar ventas con consulta de inventario.'],
['Media', 'HU9, HU10, HU11, HU13, HU17, HU18, HU19, HU21', 'Fortalecen el control del inventario, compras y atencion personalizada.'],
['Baja', 'HU14, HU15, HU16, HU22, HU23, HU24, HU25, HU26', 'Aportan analisis, alertas y control administrativo posterior.'],
], [1.0, 2.65, 3.6])

heading(doc, '3 7 Conclusiones del capitulo')
para(doc, 'El analisis del dominio identifica que el salon necesita integrar sus procesos operativos y administrativos en un sistema web. Las historias de usuario y la matriz de permisos delimitan las funciones de cada rol y sirven como base para planificar los Sprints, implementar las funcionalidades y verificar su aceptacion.')
para(doc, 'En particular, el acceso al inventario debe estar disponible para Administrador y Personal, porque ambos requieren consultar los productos durante la atencion. La modificacion de productos, proveedores, compras y reportes se mantiene restringida al Administrador para conservar el control administrativo del negocio.')

footer = sec.footer.paragraphs[0]
footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
fr = footer.add_run('Salon de Belleza Mis Uñas Valentina  Capitulo III')
fr.font.name = 'Arial'; fr.font.size = Pt(8); fr.font.color.rgb = RGBColor(90, 90, 90)

OUT.parent.mkdir(exist_ok=True)
doc.save(OUT)
print(OUT)
