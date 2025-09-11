/* =======================================================
   FlexVTC - main.js (MapLibre + OSRM + Nominatim + Autocomplete)
   ======================================================= */

// --- Nav mobile basique (si utilisé)
document.addEventListener('click', (e)=>{
  const t = e.target.closest('.nav-toggle');
  if(!t) return;
  const nav = document.getElementById('primary-nav');
  const expanded = t.getAttribute('aria-expanded') === 'true';
  t.setAttribute('aria-expanded', String(!expanded));
  if(nav) nav.hidden = expanded;
});

// -------------------------------------------------------
// Carte MapLibre (raster OSM) + marqueurs + source route
// -------------------------------------------------------
let mlMap, fromMarker, toMarker;
const ROUTE_SOURCE = 'route';
const ROUTE_LAYER  = 'route-line';

function initMapLibre() {
  const el = document.getElementById('map');
  if (!el) return;

  mlMap = new maplibregl.Map({
    container: 'map',
    center: [-1.5536, 47.2186], // Nantes [lng, lat]
    zoom: 12,
    style: {
      version: 8,
      sources: {
        osm: {
          type: 'raster',
          tiles: [
            'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
          ],
          tileSize: 256,
          attribution: '© OpenStreetMap'
        }
      },
      layers: [{ id: 'osm-tiles', type: 'raster', source: 'osm' }]
    }
  });

  mlMap.addControl(new maplibregl.NavigationControl(), 'top-right');

  // Marqueurs par défaut (déplaçables)
  fromMarker = new maplibregl.Marker({ draggable: true, color: '#1d4ed8' })
    .setLngLat([-1.5536, 47.2186]).addTo(mlMap); // Centre Nantes
  toMarker = new maplibregl.Marker({ draggable: true, color: '#ef4444' })
    .setLngLat([-1.6070, 47.1530]).addTo(mlMap); // Sud-Ouest de Nantes

  fromMarker.on('dragend', syncHiddenFromMarkers);
  toMarker.on('dragend', syncHiddenFromMarkers);

  mlMap.on('load', ()=>{
    mlMap.addSource(ROUTE_SOURCE, { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
    mlMap.addLayer({
      id: ROUTE_LAYER,
      type: 'line',
      source: ROUTE_SOURCE,
      paint: { 'line-color':'#0ea5e9', 'line-width':6, 'line-opacity':0.85 }
    });
  });
}

function syncHiddenFromMarkers(){
  const f = fromMarker.getLngLat();
  const t = toMarker.getLngLat();
  const set = (id, v) => { const el=document.getElementById(id); if(el) el.value = String(v); };
  set('from_lat', f.lat); set('from_lng', f.lng);
  set('to_lat',   t.lat); set('to_lng',   t.lng);
}

// ---------------------------------------
// Géocodage / Autocomplétion - Nominatim
// ---------------------------------------
function debounce(fn, delay=300){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), delay); }; }

async function geocode(q){
  if(!q || !q.trim()) return null;
  const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&accept-language=fr&q=${encodeURIComponent(q)}`;
  const res = await fetch(url, { headers: { 'Accept':'application/json' } });
  if(!res.ok) return null;
  const data = await res.json();
  if(!data.length) return null;
  return [parseFloat(data[0].lon), parseFloat(data[0].lat)]; // [lng,lat]
}

async function nominatimSuggest(q){
  if(!q || q.trim().length < 3) return [];
  const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=6&accept-language=fr&q=${encodeURIComponent(q)}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok) return [];
  const data = await res.json();
  return data.map(d => ({
    label: d.display_name,
    lng: parseFloat(d.lon),
    lat: parseFloat(d.lat)
  }));
}

// Monte un panneau d’autocomplétion sous l’input
function mountAutocomplete(inputEl){
  inputEl.setAttribute('autocomplete', 'off');
  inputEl.style.position = 'relative';

  let panel=null, items=[], active=-1;

  function close(){
    if(panel){ panel.remove(); panel=null; }
    items=[]; active=-1;
  }

  function open(sugs){
    close();
    if(!sugs.length) return;
    panel = document.createElement('div');
    panel.className = 'ac-panel';
    sugs.forEach((s, i)=>{
      const it = document.createElement('div');
      it.className = 'ac-item';
      it.setAttribute('role', 'option');
      it.textContent = s.label;
      it.addEventListener('mousedown', (e)=>{
        e.preventDefault();
        pick(s);
      });
      panel.appendChild(it);
    });
    // wrapper
    let wrap = inputEl.parentElement.querySelector('.ac-list');
    if(!wrap){
      wrap = document.createElement('div');
      wrap.className = 'ac-list';
      inputEl.parentElement.appendChild(wrap);
    }
    wrap.appendChild(panel);
    items = Array.from(panel.querySelectorAll('.ac-item'));
    active = -1;
  }

  const suggest = debounce(async ()=>{
    const q = inputEl.value;
    const sugs = await nominatimSuggest(q);
    open(sugs);
  }, 350);

  function pick(s){
    inputEl.value = s.label;
    // déplace marqueur + remplit cachés + calcule si l’autre adresse existe
    if(inputEl.id === 'from'){
      fromMarker.setLngLat([s.lng, s.lat]);
      syncHiddenFromMarkers();
    } else if(inputEl.id === 'to'){
      toMarker.setLngLat([s.lng, s.lat]);
      syncHiddenFromMarkers();
    }
    close();
  }

  inputEl.addEventListener('input', suggest);
  inputEl.addEventListener('focus', suggest);
  inputEl.addEventListener('blur', ()=> setTimeout(close, 120));

  inputEl.addEventListener('keydown', (e)=>{
    if(!panel) return;
    if(e.key === 'ArrowDown'){
      e.preventDefault();
      active = Math.min(active+1, items.length-1);
    } else if(e.key === 'ArrowUp'){
      e.preventDefault();
      active = Math.max(active-1, 0);
    } else if(e.key === 'Enter' && active>=0){
      e.preventDefault();
      items[active].dispatchEvent(new MouseEvent('mousedown'));
    } else { return; }
    items.forEach((el,i)=> el.setAttribute('aria-selected', i===active ? 'true' : 'false'));
  });
}

// ---------------------------------------
// Routage - OSRM (distance + durée)
// ---------------------------------------
async function routeOSRM(fromLngLat, toLngLat){
  const [flng, flat] = fromLngLat;
  const [tlng, tlat] = toLngLat;
  const url = `https://router.project-osrm.org/route/v1/driving/${flng},${flat};${tlng},${tlat}?overview=full&geometries=geojson`;
  const r = await fetch(url);
  if(!r.ok) throw new Error('OSRM indisponible');
  const j = await r.json();
  if(!j.routes || !j.routes.length) throw new Error('Pas de route trouvée');
  const route = j.routes[0];
  return { dist: route.distance, dur: route.duration, geom: route.geometry };
}

// Calcul du prix
function estimatePrice(distanceKm, durationMin, option){
  const base = 5, ckm = 1.2, cmin = 0.3;
  let total = base + distanceKm*ckm + durationMin*cmin;
  if(option==='seat') total += 3;
  if(option==='van')  total += 15;
  if(option==='xl')   total += 5;
  return Math.max(15, Math.round(total*100)/100);
}

// Affichage résultats
function formatDuration(mins){
  const h = Math.floor(mins/60), m = Math.round(mins%60);
  return h ? `${h} h ${String(m).padStart(2,'0')} min` : `${m} min`;
}
function showRouteResults(km, min, price){
  const wrap = document.getElementById('route-results');
  if(!wrap) return;
  document.getElementById('stat-distance').textContent = `${km.toFixed(2)} km`;
  document.getElementById('stat-duration').textContent = formatDuration(min);
  document.getElementById('stat-price').textContent    = `${price.toFixed(2)} €`;
  wrap.hidden = false;
}

// Action principale : tracer + calculer
async function computeAndDraw(){
  const fromVal = document.getElementById('from')?.value.trim();
  const toVal   = document.getElementById('to')?.value.trim();
  if(!fromVal || !toVal){
    alert('Veuillez saisir les adresses de départ et d’arrivée.');
    return;
  }

  const fromLL = await geocode(fromVal);
  const toLL   = await geocode(toVal);
  if(!fromLL || !toLL){
    alert('Impossible de localiser ces adresses. Essayez une autre saisie.');
    return;
  }

  fromMarker.setLngLat(fromLL);
  toMarker.setLngLat(toLL);
  syncHiddenFromMarkers();

  try{
    const r = await routeOSRM(fromLL, toLL);

    const fc = { type:'FeatureCollection', features:[{ type:'Feature', geometry:r.geom, properties:{} }] };
    mlMap.getSource(ROUTE_SOURCE).setData(fc);

    const bounds = new maplibregl.LngLatBounds();
    r.geom.coordinates.forEach(c => bounds.extend({ lng:c[0], lat:c[1] }));
    mlMap.fitBounds(bounds, { padding: 50, duration: 600 });

    const km  = Math.round((r.dist/1000)*100)/100;
    const min = Math.round(r.dur/60);
    const opt = document.getElementById('option')?.value || '';
    const price = estimatePrice(km, min, opt);

    showRouteResults(km, min, price);

    const set = (id, v) => { const el=document.getElementById(id); if(el) el.value = String(v); };
    set('distance_hidden', km);
    set('duration_hidden', min);
    set('price_hidden',    price);

  }catch(err){
    console.error(err);
    alert('Itinéraire indisponible pour le moment.');
  }
}

// Démarrage
document.addEventListener('DOMContentLoaded', ()=>{
  initMapLibre();

  // Autocomplétion sur les 2 inputs (sans conteneur spécifique requis)
  const fromInput = document.getElementById('from');
  const toInput   = document.getElementById('to');
  if(fromInput) mountAutocomplete(fromInput);
  if(toInput)   mountAutocomplete(toInput);

  document.getElementById('route-btn')?.addEventListener('click', computeAndDraw);

  // Recalcul prix si options changent et que la route existe
  document.getElementById('option')?.addEventListener('change', ()=>{
    const d = parseFloat(document.getElementById('distance_hidden')?.value || '0');
    const m = parseFloat(document.getElementById('duration_hidden')?.value || '0');
    if(d>0 && m>0){
      const price = estimatePrice(d, m, document.getElementById('option').value);
      showRouteResults(d, m, price);
      const pHidden = document.getElementById('price_hidden'); if(pHidden) pHidden.value = String(price);
    }
  });

  // Optionnel : recalcul si on bouge les marqueurs
  if(fromMarker) fromMarker.on?.('dragend', computeAndDraw);
  if(toMarker)   toMarker.on?.('dragend', computeAndDraw);

  // Empêche la soumission si la route n'a pas été calculée
  const form = document.getElementById('booking-form');
  form?.addEventListener('submit', (e)=>{
    const d = parseFloat(document.getElementById('distance_hidden')?.value || '0');
    const m = parseFloat(document.getElementById('duration_hidden')?.value || '0');
    if(!(d>0 && m>0)){
      e.preventDefault();
      alert('Veuillez d’abord tracer l’itinéraire pour calculer distance, durée et prix.');
    }
  });
});
