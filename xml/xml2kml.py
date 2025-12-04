import xml.etree.ElementTree as ET

def generar_kml_continuo(xml_input, kml_output):
    tree = ET.parse(xml_input)
    root = tree.getroot()
    
    ns_uri = root.tag[root.tag.find("{")+1 : root.tag.find("}")]
    ns = {'ns': ns_uri}

    coords = []

    for tramo in root.findall('ns:tramos/ns:tramo', ns):
        end_lat = float(tramo.find('ns:endcoords/ns:endlatitud', ns).text)
        end_lon = float(tramo.find('ns:endcoords/ns:endlongitud', ns).text)
        end_alt = float(tramo.find('ns:endcoords/ns:endheight', ns).text)
        coords.append(f"{end_lon},{end_lat},{end_alt}")

 
    kml = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
    document = ET.SubElement(kml, 'Document')
    ET.SubElement(document, 'name').text = "Circuito Mugello - Línea continua"

    placemark = ET.SubElement(document, 'Placemark')
    ET.SubElement(placemark, 'name').text = "Circuito Mugello"
    linestring = ET.SubElement(placemark, 'LineString')
    ET.SubElement(linestring, 'tessellate').text = '1'
    ET.SubElement(linestring, 'altitudeMode').text = 'absolute'
    ET.SubElement(linestring, 'coordinates').text = "\n" + "\n".join(coords) + "\n"

    ET.indent(kml, space="  ")
    ET.ElementTree(kml).write(kml_output, encoding='utf-8', xml_declaration=True)
    print(f"KML generado correctamente: {kml_output}")


generar_kml_continuo("circuitoEsquema.xml", "circuito.kml")