import xml.etree.ElementTree as ET

def generar_svg_altimetria(xml_input, svg_output):
    
    tree = ET.parse(xml_input)
    root = tree.getroot()

    ns_uri = root.tag[root.tag.find("{")+1 : root.tag.find("}")]
    ns = {'ns': ns_uri}

    
    alturas = []
    for tramo in root.findall('ns:tramos/ns:tramo', ns):
        endheight_elem = tramo.find("ns:endcoords/ns:endheight", ns)
        if endheight_elem is not None and endheight_elem.text is not None:
            endheight = int(endheight_elem.text)
            alturas.append(endheight)


    
    ancho = 800
    alto = 300
    margen = 20
    num_puntos = len(alturas)
    max_altura = max(alturas)
    min_altura = min(alturas)

    escala_y = (alto - 2 * margen) / (max_altura - min_altura)
    escala_x = (ancho - 2 * margen) / (num_puntos - 1)

    puntos = []
    for i, h in enumerate(alturas):
        x = margen + i * escala_x
        y = alto - margen - (h - min_altura) * escala_y
        puntos.append(f"{x},{y}")

    svg = f"""<svg width="{ancho}" height="{alto}" xmlns="http://www.w3.org/2000/svg">
  <polyline points="{' '.join(puntos)}" fill="none" stroke="black" stroke-width="2"/>
  <text x="{margen}" y="{margen}" font-size="14">Altimetría del circuito</text>
</svg>"""

    with open(svg_output, "w", encoding="utf-8") as f:
        f.write(svg)

    print(f"SVG generado correctamente: {svg_output}")

generar_svg_altimetria("circuitoEsquema.xml", "altimetria.svg")