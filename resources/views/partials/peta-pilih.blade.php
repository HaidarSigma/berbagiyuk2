<script>
  (() => {
    const W = @json(config('wilayah'));
    const awal = {!! ($lat0 ?? null) && ($lng0 ?? null) ? '['.((float) $lat0).', '.((float) $lng0).']' : 'null' !!};
    let batas = L.latLngBounds([W.batas.lat_min, W.batas.lng_min], [W.batas.lat_max, W.batas.lng_max]);
    let poligon = [];   // tiap poligon = daftar cincin, tiap cincin = [[lng, lat], ...]

    const map = L.map('map', { maxBounds: batas.pad(0.15), maxBoundsViscosity: 1.0, minZoom: 9 })
      .setView(awal ?? W.pusat, awal ? 14 : W.zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
    let marker = awal ? L.marker(awal).addTo(map) : null;

    const gaya = { color: '#15803d', weight: 2, fillColor: '#16a34a', fillOpacity: 0.07, interactive: false };

    const dalamCincin = (lat, lng, c) => {
      let di = false;
      for (let i = 0, j = c.length - 1; i < c.length; j = i++) {
        const [xi, yi] = c[i], [xj, yj] = c[j];
        if ((yi > lat) !== (yj > lat) && lng < (xj - xi) * (lat - yi) / (yj - yi) + xi) di = !di;
      }
      return di;
    };
    const dalam = (lat, lng) => {
      if (!poligon.length) return batas.contains([lat, lng]);
      return poligon.some(p => dalamCincin(lat, lng, p[0]) && !p.slice(1).some(h => dalamCincin(lat, lng, h)));
    };
    const kumpul = n => {
      if (!n) return [];
      if (n.type === 'FeatureCollection') return n.features.flatMap(kumpul);
      if (n.type === 'Feature') return kumpul(n.geometry);
      if (n.type === 'Polygon') return [n.coordinates];
      if (n.type === 'MultiPolygon') return n.coordinates;
      return [];
    };

    // Batas wilayah sebenarnya; bila berkas tidak ada, pakai kotak batas
    fetch('/geojson/wilayah.geojson')
      .then(r => (r.ok ? r.json() : null))
      .then(geo => {
        if (!geo) { L.rectangle(batas, gaya).addTo(map); return; }
        poligon = kumpul(geo);
        const lapis = L.geoJSON(geo, { style: () => gaya, interactive: false }).addTo(map);
        batas = lapis.getBounds();
        map.setMaxBounds(batas.pad(0.15));
        if (!awal) map.fitBounds(batas);
      })
      .catch(() => L.rectangle(batas, gaya).addTo(map));

    map.on('click', e => {
      if (!dalam(e.latlng.lat, e.latlng.lng)) {
        alert('Titik harus berada di wilayah layanan ' + W.nama + ' (area berwarna hijau).');
        return;
      }
      marker ? marker.setLatLng(e.latlng) : marker = L.marker(e.latlng).addTo(map);
      document.getElementById('lat').value = e.latlng.lat.toFixed(7);
      document.getElementById('lng').value = e.latlng.lng.toFixed(7);
    });
  })();
</script>