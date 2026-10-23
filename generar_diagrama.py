from PIL import Image, ImageDraw, ImageFont
from pathlib import Path
W,H=2920,2140
im=Image.new('RGB',(W,H),'#f7f8fa'); d=ImageDraw.Draw(im)
def f(s,b=False): return ImageFont.truetype('C:/Windows/Fonts/'+('arialbd.ttf' if b else 'arial.ttf'),s)
title,sub,head,body,note=f(44,1),f(24),f(26,1),f(20),f(18)
cards={
'Clientes':(70,150,['id_cliente: int {PK}','nombre: varchar(50)','apellido: varchar(100)','telefono: varchar(20)','email: varchar(50)']),
'Reservas':(760,150,['id_reserva: int {PK}','id_cliente: int {FK}','id_servicio: int {FK}','id_usuario: int {FK}','fecha: date','hora: time','subtotal: varchar(60)','adelanto: decimal(10,2)','estado: varchar(20)']),
'Atencion':(1450,150,['id_atencion: int {PK}','id_cliente: int {FK}','id_servicio: int {FK}','id_usuario: int {FK}','fecha: date · hora: time','observaciones: varchar(255)','estado: enum','total: decimal(10,2)']),
'Usuario':(2140,150,['id_usuario: int {PK}','username: varchar(50)','email: varchar(100)','password: varchar(255)','rol: varchar(20)','pago: varchar(20)','estado: varchar(20)','fecha_creacion: timestamp']),
'Servicios':(70,620,['id_servicio: int {PK}','nombre: varchar(50)','descripcion: varchar(100)','precio: decimal(10,2)']),
'Especialidades':(760,620,['id_especialidad: int {PK}','id_usuario: int {FK}','id_servicio: int {FK}','UNIQUE(id_usuario, id_servicio)']),
'Ventas':(1450,620,['id_venta: int {PK}','id_cliente: int {FK}','id_usuario: int {FK}','fecha: date','total: decimal(10,2)']),
'DetalleVenta':(2140,620,['id_detalle: int {PK}','id_venta: int {FK}','id_producto: int {FK}','cantidad: int','precioventa: decimal(10,2)']),
'Proveedores':(70,1090,['id_proveedor: int {PK}','nombre: varchar(100)','apellido: varchar(100)','telefono: varchar(20)','direccion: text','email: varchar(100)']),
'Compras':(760,1090,['id_compra: int {PK}','id_proveedor: int {FK, nullable}','id_usuario: int*','total: decimal(10,2)','fecha: datetime']),
'DetalleCompra':(1450,1090,['id_detalle: int {PK}','id_compra: int {FK}','id_producto: int {FK}','cantidad: int','precio: decimal(10,2)']),
'Producto':(2140,1090,['id_producto: int {PK}','descripcion: text','color: varchar(50)','imagen: varchar(255)','stock: int','preciocompra: decimal(10,2)','precioventa: decimal(10,2)','id_categoria: int {FK}','idmarca: int {FK}','id_proveedor: int {FK}','idlote: int*']),
'Categorias':(760,1560,['id_categoria: int {PK}','nombre: varchar(50)']),
'Marca':(1450,1560,['idmarca: int {PK}','nombre: varchar(200)']),
'LoteProducto':(2140,1560,['idlote: int {PK}','idproducto: int {FK}','fecha_caducidad: date','cantidad_producto_lote: varchar(200)'])}
CW=560
def ht(fs): return 80+34*len(fs)
def center(n):
 x,y,fs=cards[n]; return x+CW//2,y+ht(fs)//2
def ends(a,b):
 ax,ay=center(a); bx,by=center(b); dx,dy=bx-ax,by-ay
 if abs(dx)>abs(dy): return ((cards[a][0]+(CW if dx>0 else 0),ay),(cards[b][0]+(0 if dx>0 else CW),by))
 return ((ax,cards[a][1]+(ht(cards[a][2]) if dy>0 else 0)),(bx,cards[b][1]+(0 if dy>0 else ht(cards[b][2]))))
edges=[('Clientes','Reservas','realiza'),('Clientes','Atencion','solicita'),('Clientes','Ventas','participa'),('Usuario','Reservas','gestiona'),('Usuario','Atencion','atiende'),('Usuario','Ventas','registra'),('Usuario','Especialidades','tiene'),('Servicios','Reservas','se reserva'),('Servicios','Atencion','se atiende'),('Servicios','Especialidades','define'),('Ventas','DetalleVenta','contiene'),('Compras','DetalleCompra','contiene'),('Producto','DetalleVenta','aparece en'),('Producto','DetalleCompra','aparece en'),('Proveedores','Compras','suministra'),('Proveedores','Producto','provee'),('Categorias','Producto','clasifica'),('Marca','Producto','identifica'),('LoteProducto','Producto','pertenece a')]
# Draw associations before cards so connectors never cover text.
for a,b,label in edges:
 p1,p2=ends(a,b); m=((p1[0]+p2[0])//2,(p1[1]+p2[1])//2)
 d.line([p1,m,p2],fill='#81909d',width=3)
 bb=d.textbbox((0,0),label,font=note); tw=bb[2]-bb[0]
 d.rounded_rectangle((m[0]-tw//2-5,m[1]-13,m[0]+tw//2+5,m[1]+12),radius=4,fill='#f7f8fa')
 d.text((m[0]-tw//2,m[1]-11),label,font=note,fill='#33495d')
d.rounded_rectangle((40,25,W-40,115),radius=12,fill='#263b4d'); d.text((75,38),'DIAGRAMA DE CLASES PERSISTENTES',font=title,fill='white'); d.text((77,86),'Miss Uñas Valentina  ·  Base de datos bdbella',font=sub,fill='#dce6ee')
for name,(x,y,fs) in cards.items():
 h=ht(fs); d.rounded_rectangle((x,y,x+CW,y+h),radius=7,fill='#fff8f3',outline='#303b45',width=3); d.rounded_rectangle((x,y,x+CW,y+60),radius=7,fill='#f0ddcf',outline='#303b45',width=3); d.rectangle((x,y+52,x+CW,y+62),fill='#f0ddcf'); bb=d.textbbox((0,0),name,font=head); tw=bb[2]-bb[0]; d.text((x+(CW-tw)//2,y+16),name,font=head,fill='#18232d'); d.line((x,y+61,x+CW,y+61),fill='#303b45',width=2)
 for i,field in enumerate(fs): d.text((x+20,y+76+i*34),'- '+field,font=body,fill='#20272e')
fy=1990; d.line((60,fy-20,W-60,fy-20),fill='#aab4be',width=2); d.text((70,fy),'{PK} clave primaria     {FK} clave foránea declarada     1 — uno     0..* — cero o muchos',font=sub,fill='#263746'); d.text((70,fy+42),'* Referencias presentes sin FK declarada: compras.id_usuario y productos.idlote.',font=note,fill='#46535f'); d.text((70,fy+70),'Se muestran los atributos de CREATE TABLE; las relaciones corresponden a las FK declaradas.',font=note,fill='#46535f')
out=Path('diagrama_clases_persistentes.png'); im.save(out,dpi=(160,160)); print(out.resolve())
