import xml.etree.ElementTree as ET

class Html:
    def __init__(self, title="Info Circuito"):
        self.title = title
        self.parts = []

    def add(self, html):
        self.parts.append(html)

    def header(self):
        return f'''<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name = "author" content = "Sergio Blanco García"/>
  <meta name = "viewport" content = "width = device-width, initial-scale = 1.0"/>
  <meta name = "keywords" content = "circuito"/>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{self.title}</title>
  <!-- Enlazar al CSS del proyecto "MotoGP Desktop" -->
  <link rel="stylesheet" href="MotoGP Desktop/estilo.css">
</head>
<body>
  <header>
    <h1>{self.title}</h1>
    <nav>
            <a href = "index.html">Inicio</a>
            <a href = "piloto.html">Piloto</a>
            <a href = "circuito.html">Circuito</a>
            <a href = "meteorologia.html">Meteorología</a>
            <a href = "clasificaciones.html">Clasificaciones</a>
            <a href = "juegos.html">Juegos</a>
            <a href = "ayuda.html">Ayuda</a>
      </nav>
  </header>
  <main>'''

    def footer(self):
        return '''
  </main>
</body>
</html>'''

    def render(self):
        return self.header() + "\n".join(self.parts) + self.footer()

def text_of(elem, ns, path, default=""):
    found = elem.find(path, namespaces=ns)
    return found.text.strip() if found is not None and found.text is not None else default

def main(x, y):
    tree = ET.parse(x)
    root = tree.getroot()

    
    ns = {'u': 'http://www.uniovi.es'}

    html = Html(title="Info del Circuito - MotoGP")

    
    name = text_of(root, ns, 'u:name')
    length = root.find('u:length', ns)
    length_val = length.text.strip() if length is not None else ''
    length_unit = length.get('unit') if length is not None else ''
    width = root.find('u:width', ns)
    width_val = width.text.strip() if width is not None else ''
    width_unit = width.get('unit') if width is not None else ''
    date = text_of(root, ns, 'u:date')
    hour = text_of(root, ns, 'u:hour')
    laps = text_of(root, ns, 'u:laps')
    location = text_of(root, ns, 'u:location')
    country = text_of(root, ns, 'u:country')
    sponsor = text_of(root, ns, 'u:sponsor')

    intro = f'''
            <section>
              <h2>Información básica del circuito</h2>
              <dl>
                <dt>Nombre</dt>
                <dd>{name}</dd>
                <dt>Longitud</dt>
                <dd>{(length_val)} {(length_unit)}</dd>
                <dt>Ancho</dt>
                <dd>{(width_val)} {(width_unit)}</dd>
                <dt>Fecha</dt>
                <dd>{(date)}</dd>
                <dt>Hora</dt>
                <dd>{(hour)}</dd>
                <dt>Vueltas</dt>
                <dd>{(laps)}</dd>
                <dt>Ubicación</dt>
                <dd>{(location)}</dd>
                <dt>País</dt>
                <dd>{(country)}</dd>
                <dt>Patrocinador</dt>
                <dd>{(sponsor)}</dd>
              </dl>
            </section>'''
    html.add(intro)

    refs = root.findall('u:references/u:reference', ns)
    if refs:
        rlist = '<ul>'
        for r in refs:
            url = (r.text or '').strip()
            rlist += f'  <li><a href="{(url)}"></a></li>'
        rlist += '</ul>'
        html.add(f'''
          <section>
            <h2>Referencias</h2>
            {rlist}
          </section>''')
    photos = root.findall('u:photos/u:photo', ns)
    if photos:
        pict = ''
        for p in photos:
            title = text_of(p, ns, 'u:phototitle')
            url = text_of(p, ns, 'u:imgurl')
            pict += f'''
              <img src="{(url)}" alt="{(title)}"/>'''
        pict += ''
        html.add(f'''
          <section>
            <h2>Fotos</h2>
            {pict}
          </section>''')

    videos = root.findall('u:videos/u:video', ns)
    if videos:
        vids = ''
        for v in videos:
            vtitle = text_of(v, ns, 'u:videotitle')
            vfile = text_of(v, ns, 'u:filename')
            vurl = text_of(v, ns, 'u:url')
            vids += f'''
              <h3>{(vtitle)}</h3>
              <video controls>
                <source src="{(vurl)}" type="{(text_of(v, ns, 'u:format'))}"/>
                Tu navegador no soporta video.
              </video>
              <p>Archivo: {(vfile)}</p>'''
    html.add(f'''
          <section>
            <h2>Videos</h2>
            {vids}
          </section>''')

    winner = text_of(root, ns, 'u:winnerinfo/u:winner')
    wtime = text_of(root, ns, 'u:winnerinfo/u:time')
    if winner or wtime:
        html.add(f'''
          <section>
            <h2>Ganador</h2>
            <p>{(winner)} — Tiempo: {(wtime)}</p>
          </section>''')

    qualified = root.findall('u:qualified/u:driver', ns)
    if qualified:
        qlist = '<ol>'
        for d in qualified:
            qlist += f'  <li>{((d.text or "").strip())}</li>'
        qlist += '</ol>'
        html.add(f'''
          <section>
            <h2>Clasificados</h2>
            {qlist}
          </section>''')

    output = html.render()
    with open(y, 'w', encoding='utf-8') as f:
        f.write(output)

if __name__ == '__main__':
    main('circuitoEsquema.xml','InfoCircuito.html')
