"use strict";

/* ═══════════════════════════════════════════════════════════════
   EURO AUTOS — InspectionApp v2
   Fully OOP architecture
   ═══════════════════════════════════════════════════════════════ */

// ── CONSTANTS ──────────────────────────────────────────────────
const PARTS_DB = {
  "PARTE DELANTERA": [
    "Bancada","Guardabarro","Base Metálica Farola","Guardapolvo Metal",
    "Bigote","Guardapolvo Plástico","Bisagra","Guaya Tapa Motor",
    "Bisel Bocallantas","Guía Bómper","Bisel Bómper","Luz Media",
    "Bisel Farola","Marco Parabrisas","Bisel de Persiana","Medio G/Polvo Metálico",
    "Bisel Guardabarros","Parabrisas","Bómper","Paral Chapa",
    "Brazo Plumilla","Persiana","Broches Bómper","Piso Delantero",
    "Broches y Plástico","Plumilla","Calandra","Punta Chasis",
    "Caucho de Bómper","Refuerzo G/Fango","Chapa Tapa Motor","Rejilla Capó",
    "Deflector Bómper","Rejilla Torpedo","Elemento Cierre","Sikaflex",
    "Emblema Persiana","Soporte de Bumper","Emblema Lateral","Soporte Farola",
    "Empaque Parabrisas","Tapa Motor","Enderezada","Torpedo",
    "Estiraje","Torre","Exploradora","Traviesa","Farola","Trompo Capó","Garnish",
  ],
  "MOTOR Y OTROS": [
    "Alternador","Guaya Caja Velocidad","Aceite Motor","Líquido Frenos",
    "Aceite Caja","Manguera Inferior Radiador","Base Alternador","Manguera Superior Radiador",
    "Base Batería","Módulo","Batería","Motor","Botella","Motor Limpiabrisas",
    "Caja Dirección","Motor Ventilador","Caja Velocidades","Pito",
    "Caja Dirección Hidráulica","Puente Caja Dirección","Caja Fusibles","Purificador",
    "Calefacción Completa","Radiador Agua","Canister","Radiador Aire Acondicionado",
    "Carcaza Caja Velocidades","Relay","Carcaza Calefacción","Refrigerante",
    "Coca Distribuidor","Soporte Caja Velocidad","Compresor Aire Acond.","Soporte Motor",
    "Coraza Radiador","Soporte Radiador","Correa Alternador","Tapa Culata",
    "Culata","Tapa Radiador","Depósito Agua Limpiavidrios","Tensor A/A",
    "Depósito Agua Radiador","Triceta Eje","Depósito Líquido Frenos","Tubo de Alta presión",
    "Distribuidor","Tubo de Baja","Ducto Aire","Varilla Tapa Motor","Filtro de Aire",
  ],
  "CABINA": [
    "Bisel Capota","Placa Oval","Air Bag","Portapaquetes","Cabina","Radio",
    "Capota","Refuerzo Capota","Calculador","Rejilla Carcaza","Carcaza Tablero",
    "Sensor Lluvia","Cenicero Consola","Swiche Encendido","Cintilla","Swiche Limpiabrisas",
    "Cinturón Seguridad","Swiche Luces","Coca Espejo","Tablero Instrumentos",
    "Cojín Delantero","Tablero","Cojín Trasero","Tapa Gaveta","Consola Central",
    "Tapa Swiche Superior","Escanear","Tapasol","Espejo Interior","Tapete Delantero",
    "Espejo Lateral","Tapete Trasero","Gaveta","Tapizado Capota","Luna Espejo",
    "Visera Tablero","Luz Techo","Palanca Emergencia",
  ],
  "PARTE TRASERA": [
    "Babero","Nave","Bisagra","Piso Trasero","Bisel Bocallantas","Plumilla Trasera",
    "Bisel Bómper","Portarepuesto","Bisel Nave","Portastop Metálicos","Bómper Trasero",
    "Punta Chasis Trasera","Brazo Plumilla Trasera","Punteras","Caucho Bómper",
    "Recibidor Tapa Maleta","Chapa Tapa Maleta","Refuerzo Baúl","Compuerta",
    "Rejilla Faldón","Empaque Baúl","Spoiler Tapa Maleta","Empaque Tapa Maleta",
    "Sellante","Empaque Vidrio Fijo","Soportes Bumper","Empaque Vidrio Parabrisas",
    "Stop","Faldón","Tapa Baúl de Maleta","Frasco Limpiabrisas Trasero",
    "Vidrio Fijo Lateral","Guarda Polvo Metálico","Vidrio Parabrisas Trasero",
    "Luz Placa","Marco Compuerta","Motor Limpiabrisas Trasero",
  ],
  "PUERTAS": [
    "Bisel Estribo","Bisel Puerta","Bisagra Puerta","Broche Puerta",
    "Cartera o Tapizado","Calcomanía Puerta","Chapa","Cilindro Chapa","Cremallera",
    "Empaque Puerta","Estribo","Guarnecido Paral","Lamevidrios","Manija Elevavidrios",
    "Manija Externa","Marco Puertas","Marco Vidrios","Motor Elevavidrios",
    "Paral Central","Paral Custodia","Paral Frontal","Paral Trasero",
    "Portamapas","Puerta","Recibidor Chapa","Vidrio de Puerta",
  ],
  "SUSPENSIÓN Y OTROS": [
    "Abrazadera","Amortiguador","Barra de Torsión","Barra Estabilizadora",
    "Base Tijera","Bomba Aceite","Brazo Axial","Brazo de Dirección",
    "Buje Barra Estabilizadora","Cacho Trasero","Campana","Cárter","Copa Rin",
    "Cuna","Disco Freno","Eje","Eje Retráctil","Esfera","Espiral","Filtro de Aceite",
    "Gallete Gasolina","Guarda Polvo Eje","Llantas","Manguera T Gasolina",
    "Pasador Tijera","Porta Mangueta","Protector Cárter","Protector T Gasolina",
    "Puente Motor","Rin","Rodillo","Soporte Mofle","Tarro Mofle",
    "Tensor Tijera Central","Tijera Inferior","Tijera Superior","Troque Trasero",
  ],
};

const ACTIONS = {
  LEVE:    "Reparación Leve",
  MEDIA:   "Reparación Media",
  FUERTE:  "Reparación Fuerte",
  CAMBIO:  "Cambio",
  PINTURA: "Pintura",
  DESMONTAJE_MONTAJE: "Desmontaje / Montaje",
  REVISION: "Revisión",
};

const SIDE_LABELS = {
  DEL: "Delantero", TRA: "Trasero", IZQ: "Izquierdo",
  DER: "Derecho",  CEN: "Central", SUP: "Superior", INF: "Inferior",
};

const SIDE_COMBOS = {
  "DEL+DER":"Del. Derecho","DEL+IZQ":"Del. Izquierdo",
  "TRA+DER":"Tra. Derecho","TRA+IZQ":"Tra. Izquierdo",
  "CEN+DEL":"Central Del.","CEN+TRA":"Central Tra.",
  "CEN+DER":"Central Der.","CEN+IZQ":"Central Izq.",
  "DEL+SUP":"Del. Superior","DEL+INF":"Del. Inferior",
  "TRA+SUP":"Tra. Superior","TRA+INF":"Tra. Inferior",
  "IZQ+SUP":"Lat. Izq. Sup.","DER+SUP":"Lat. Der. Sup.",
  "IZQ+INF":"Lat. Izq. Inf.","DER+INF":"Lat. Der. Inf.",
  "DEL+DER+SUP":"Del. Der. Sup.","DEL+IZQ+SUP":"Del. Izq. Sup.",
  "TRA+DER+SUP":"Tra. Der. Sup.","TRA+IZQ+SUP":"Tra. Izq. Sup.",
  "DEL+DER+INF":"Del. Der. Inf.","DEL+IZQ+INF":"Del. Izq. Inf.",
  "TRA+DER+INF":"Tra. Der. Inf.","TRA+IZQ+INF":"Tra. Izq. Inf.",
};

const SIDE_ORDER = ["DEL", "TRA", "CEN", "IZQ", "DER", "SUP", "INF"];


/* ═══════════════════════════════════════════════════════════════
   CLASS: SideSelector — manages the vehicle diagram popup
   ═══════════════════════════════════════════════════════════════ */
class SideSelector {
  constructor(instanceKey, partName, onChange) {
    this.key      = instanceKey;
    this.partName = partName;
    this.onChange = onChange;
    this.active   = new Set();
    this._modalEl = null;
  }

  static _comboLabel(sidesSet) {
    const arr = [...sidesSet].sort((a, b) => SIDE_ORDER.indexOf(a) - SIDE_ORDER.indexOf(b));
    if (!arr.length) return null;
    if (arr.length === 7) return "Todos los lados";
    if (arr.length === 1) return SIDE_LABELS[arr[0]];
    return SIDE_COMBOS[arr.join("+")] || arr.map(s => SIDE_LABELS[s]).join(" + ");
  }

  static _comboKey(sidesSet) {
    return [...sidesSet].sort((a, b) => SIDE_ORDER.indexOf(a) - SIDE_ORDER.indexOf(b)).join("+");
  }

  get label() {
    return SideSelector._comboLabel(this.active) || "Seleccionar lados";
  }

  get comboKey() {
    return SideSelector._comboKey(this.active);
  }

  toggle(side) {
    if (this.active.has(side)) this.active.delete(side);
    else this.active.add(side);
    this._refresh();
    this.onChange();
  }

  remove(side) {
    this.active.delete(side);
    this._refresh();
    this.onChange();
  }

  _refresh() {
    if (!this._modalEl) return;
    // Update diagram buttons
    this._modalEl.querySelectorAll(".vdz").forEach(btn => {
      btn.classList.toggle("on", this.active.has(btn.dataset.side));
    });
    // Chips
    const chipsEl = this._modalEl.querySelector(".vd-chips-row");
    chipsEl.innerHTML = "";
    if (this.active.size === 0) {
      chipsEl.innerHTML = '<span style="color:var(--text-3);font-size:0.72rem;font-family:\'Space Mono\',monospace;">Toca el diagrama para seleccionar</span>';
    } else {
      [...this.active].forEach(s => {
        const chip = document.createElement("span");
        chip.className = "vd-chip";
        chip.innerHTML = `${SIDE_LABELS[s]} <button class="vd-chip-rm" data-side="${s}">×</button>`;
        chip.querySelector(".vd-chip-rm").addEventListener("click", () => this.remove(s));
        chipsEl.appendChild(chip);
      });
    }
    // Combo label
    const comboEl = this._modalEl.querySelector(".vd-combo-display");
    const lbl = SideSelector._comboLabel(this.active);
    comboEl.innerHTML = lbl ? `Posición: <strong>${lbl}</strong>` : "";
  }

  buildModalHTML() {
    return `
    <div class="modal-overlay" id="side-modal-${this._safeKey()}">
      <div class="modal-box">
        <div class="modal-head">
          <h3><i class="fas fa-crosshairs"></i> Lados — <em>${this.partName}</em></h3>
          <button class="btn-close-modal" data-action="close-side-modal" data-key="${this.key}">×</button>
        </div>
        <div class="modal-body">
          <div class="vd-diagrams">
            <!-- Vista Superior -->
            <div class="vd-view">
              <span class="vd-view-label">Vista Superior</span>
              <div class="vd-area" style="width:130px;height:160px;">
                <svg width="130" height="160" viewBox="0 0 130 160" style="position:absolute;pointer-events:none;">
                  <rect x="24" y="10" width="82" height="140" rx="16" fill="#1e2535" stroke="#3a4560" stroke-width="1.5"/>
                  <rect x="35" y="16" width="60" height="32" rx="6" fill="#2a3a5c" opacity="0.8"/>
                  <rect x="35" y="112" width="60" height="32" rx="6" fill="#2a3a5c" opacity="0.5"/>
                  <line x1="24" y1="80" x2="106" y2="80" stroke="#3a4560" stroke-width="1" stroke-dasharray="3 2"/>
                  <rect x="7" y="19" width="17" height="28" rx="5" fill="#2a3347"/>
                  <rect x="7" y="113" width="17" height="28" rx="5" fill="#2a3347"/>
                  <rect x="106" y="19" width="17" height="28" rx="5" fill="#2a3347"/>
                  <rect x="106" y="113" width="17" height="28" rx="5" fill="#2a3347"/>
                  <polygon points="65,3 69,10 61,10" fill="#5c6b8a" opacity="0.7"/>
                </svg>
                <button class="vdz" data-side="DEL" style="top:10px;left:24px;width:82px;height:36px;border-radius:10px 10px 4px 4px;">DEL</button>
                <button class="vdz" data-side="TRA" style="bottom:10px;left:24px;width:82px;height:36px;border-radius:4px 4px 10px 10px;">TRA</button>
                <button class="vdz" data-side="IZQ" style="top:50px;left:0;width:26px;height:60px;writing-mode:vertical-rl;letter-spacing:1px;">IZQ</button>
                <button class="vdz" data-side="DER" style="top:50px;right:0;width:26px;height:60px;writing-mode:vertical-rl;letter-spacing:1px;">DER</button>
                <button class="vdz" data-side="CEN" style="top:56px;left:36px;width:58px;height:48px;border-radius:8px;">CEN</button>
              </div>
            </div>
            <!-- Vista Lateral -->
            <div class="vd-view">
              <span class="vd-view-label">Vista Lateral</span>
              <div class="vd-area" style="width:190px;height:80px;">
                <svg width="190" height="80" viewBox="0 0 190 80" style="position:absolute;pointer-events:none;">
                  <path d="M22,56 Q22,22 52,16 L138,16 Q168,16 168,40 L168,56 Z" fill="#1e2535" stroke="#3a4560" stroke-width="1.5"/>
                  <path d="M56,17 Q56,28 63,30 L124,30 Q130,30 132,22 L128,17 Z" fill="#2a3a5c" opacity="0.7"/>
                  <circle cx="53" cy="60" r="14" fill="#2a3347"/>
                  <circle cx="53" cy="60" r="7" fill="#3a4560"/>
                  <circle cx="137" cy="60" r="14" fill="#2a3347"/>
                  <circle cx="137" cy="60" r="7" fill="#3a4560"/>
                  <line x1="5" y1="74" x2="185" y2="74" stroke="#3a4560" stroke-width="1"/>
                </svg>
                <button class="vdz" data-side="SUP" style="top:1px;left:44px;width:102px;height:22px;border-radius:6px 6px 3px 3px;">SUP</button>
                <button class="vdz" data-side="INF" style="top:52px;left:70px;width:50px;height:18px;border-radius:3px 3px 6px 6px;">INF</button>
              </div>
            </div>
          </div>
          <div class="vd-chips-row">
            <span style="color:var(--text-3);font-size:0.72rem;font-family:'Space Mono',monospace;">Toca el diagrama para seleccionar</span>
          </div>
          <div class="vd-combo-display"></div>
        </div>
        <div class="modal-foot">
          <button class="btn-primary" data-action="close-side-modal" data-key="${this.key}">
            <i class="fas fa-check"></i> Confirmar
          </button>
        </div>
      </div>
    </div>`;
  }

  _safeKey() { return this.key.replace(/[^a-zA-Z0-9]/g, "_"); }

  mount(container) {
    container.insertAdjacentHTML("beforeend", this.buildModalHTML());
    this._modalEl = document.getElementById(`side-modal-${this._safeKey()}`);
    // Bind diagram buttons
    this._modalEl.querySelectorAll(".vdz").forEach(btn => {
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggle(btn.dataset.side);
      });
    });
    // Close buttons
    this._modalEl.querySelectorAll("[data-action='close-side-modal']").forEach(btn => {
      btn.addEventListener("click", () => this.close());
    });
    // Backdrop click
    this._modalEl.addEventListener("click", (e) => {
      if (e.target === this._modalEl) this.close();
    });
  }

  open() {
    if (!this._modalEl) return;
    this._refresh();
    this._modalEl.classList.add("open");
  }

  close() {
    if (!this._modalEl) return;
    this._modalEl.classList.remove("open");
  }

  restoreState(sidesArray) {
    this.active = new Set(sidesArray);
  }

  toJSON() {
    return [...this.active];
  }
}


/* ═══════════════════════════════════════════════════════════════
   CLASS: PartInstance — one damage instance of a part
   ═══════════════════════════════════════════════════════════════ */
class PartInstance {
  constructor(instanceKey, partName, category, onUpdate, onRemove) {
    this.key      = instanceKey;
    this.partName = partName;
    this.category = category;
    this.onUpdate = onUpdate;
    this.onRemove = onRemove;
    this.note     = "";
    this.actions  = []; // [{action, note}]

    this.sideSelector = new SideSelector(instanceKey, partName, () => {
      this._syncComboRow();
      this.onUpdate();
    });

    this._el = null;
  }

  _safeKey() { return this.key.replace(/[^a-zA-Z0-9]/g, "_"); }

  get hasData() {
    return this.sideSelector.active.size > 0 || this.actions.length > 0;
  }

  render(container) {
    const sk = this._safeKey();
    const num = this.key.split("#").pop();
    const div = document.createElement("div");
    div.className = "instance-block";
    div.id = `inst-${sk}`;
    div.innerHTML = `
      <div class="instance-bar">
        <span class="instance-tag">
          <i class="fas fa-wrench"></i>&nbsp;
          ${this.partName}&nbsp;<span class="instance-num">#${parseInt(num)+1}</span>
        </span>
        <button class="btn-del-inst" id="del-inst-${sk}" title="Eliminar">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="instance-body">
        <button class="side-trigger" id="side-trigger-${sk}">
          <i class="fas fa-arrows-alt"></i>
          <span class="side-trigger-label" id="side-label-${sk}">Seleccionar lados</span>
          <i class="fas fa-chevron-right" style="font-size:0.65rem;margin-left:auto;opacity:0.4;"></i>
        </button>
        <div class="damage-zone" id="damage-zone-${sk}"></div>
        <input class="inst-note" id="inst-note-${sk}" placeholder="Nota adicional de esta pieza..." />
      </div>
    `;
    this._el = div;
    container.appendChild(div);

    // Mount side modal
    this.sideSelector.mount(document.body);

    // Events
    div.querySelector(`#del-inst-${sk}`).addEventListener("click", () => this._remove());
    div.querySelector(`#side-trigger-${sk}`).addEventListener("click", () => this.sideSelector.open());
    div.querySelector(`#inst-note-${sk}`).addEventListener("input", (e) => {
      this.note = e.target.value;
      this.onUpdate();
    });
  }

  _remove() {
    if (this._el) this._el.remove();
    const modal = document.getElementById(`side-modal-${this._safeKey()}`);
    if (modal) modal.remove();
    this.onRemove(this.key);
  }

  _syncComboRow() {
    const sk = this._safeKey();
    const zone = document.getElementById(`damage-zone-${sk}`);
    if (!zone) return;

    const sides = this.sideSelector.active;
    const trigger = document.getElementById(`side-trigger-${sk}`);
    const labelEl = document.getElementById(`side-label-${sk}`);

    // Update trigger
    if (labelEl) labelEl.textContent = this.sideSelector.label;
    if (trigger) trigger.classList.toggle("has-sides", sides.size > 0);

    if (sides.size === 0) {
      zone.innerHTML = "";
      this.actions = [];
      return;
    }

    // If no combo row yet, create it
    let comboRow = zone.querySelector(".combo-row");
    if (!comboRow) {
      comboRow = document.createElement("div");
      comboRow.className = "combo-row";
      comboRow.innerHTML = `
        <div class="combo-row-head">
          <span class="combo-label" id="combo-lbl-${sk}">${this.sideSelector.label}</span>
          <button class="btn-add-action" id="add-action-${sk}">
            <i class="fas fa-plus"></i> Acción
          </button>
        </div>
        <div class="combo-row-body" id="actions-body-${sk}"></div>
      `;
      zone.appendChild(comboRow);
      comboRow.querySelector(`#add-action-${sk}`).addEventListener("click", () => this._addActionRow());
      // Start with one action row
      this._addActionRow();
    } else {
      // Just update label
      const lbl = document.getElementById(`combo-lbl-${sk}`);
      if (lbl) lbl.textContent = this.sideSelector.label;
    }
  }

  _addActionRow() {
    const sk = this._safeKey();
    const body = document.getElementById(`actions-body-${sk}`);
    if (!body) return;

    const rowEl = document.createElement("div");
    rowEl.className = "action-row";

    const sel = document.createElement("select");
    sel.className = "action-select";
    sel.innerHTML = `<option value="">— Acción —</option>` +
      Object.entries(ACTIONS).map(([v, l]) => `<option value="${v}">${l}</option>`).join("");
    sel.addEventListener("change", (e) => {
      sel.dataset.action = e.target.value;
      this._collectActions();
      this.onUpdate();
    });

    const note = document.createElement("input");
    note.className = "action-note";
    note.placeholder = "Nota";
    note.addEventListener("input", () => { this._collectActions(); this.onUpdate(); });

    const del = document.createElement("button");
    del.className = "btn-del-action";
    del.innerHTML = `<i class="fas fa-trash-alt"></i>`;
    del.addEventListener("click", () => { rowEl.remove(); this._collectActions(); this.onUpdate(); });

    rowEl.appendChild(sel);
    rowEl.appendChild(note);
    rowEl.appendChild(del);
    body.appendChild(rowEl);
  }

  _collectActions() {
    const sk = this._safeKey();
    const body = document.getElementById(`actions-body-${sk}`);
    if (!body) { this.actions = []; return; }
    this.actions = [...body.querySelectorAll(".action-row")]
      .map(row => ({
        accion: row.querySelector(".action-select")?.value || "",
        nota:   row.querySelector(".action-note")?.value   || "",
      }))
      .filter(a => a.accion);
  }

  toJSON() {
    this._collectActions();
    return {
      lados: { [this.sideSelector.comboKey]: this.actions },
      notas: this.note,
    };
  }

  restoreFrom(data) {
    // data = {lados: {comboKey: [{accion, nota}]}, notas}
    if (!data) return;
    const sk = this._safeKey();

    // Restore sides from combo key
    const firstCombo = Object.keys(data.lados || {})[0] || "";
    const sides = firstCombo ? firstCombo.split("+").filter(Boolean) : [];
    this.sideSelector.restoreState(sides);

    // Update trigger immediately
    const trigger = document.getElementById(`side-trigger-${sk}`);
    const labelEl = document.getElementById(`side-label-${sk}`);
    if (labelEl) labelEl.textContent = this.sideSelector.label;
    if (trigger) trigger.classList.toggle("has-sides", sides.length > 0);

    // Build combo row with saved actions
    this._syncComboRow();

    const savedActions = (data.lados[firstCombo] || []);
    if (savedActions.length > 0) {
      const sk2 = this._safeKey();
      const body = document.getElementById(`actions-body-${sk2}`);
      if (body) {
        body.innerHTML = "";
        savedActions.forEach(ac => {
          this._addActionRow();
          const lastRow = body.lastElementChild;
          if (lastRow) {
            const sel = lastRow.querySelector(".action-select");
            const nt  = lastRow.querySelector(".action-note");
            if (sel) { sel.value = ac.accion; sel.dataset.action = ac.accion; }
            if (nt)  nt.value = ac.nota || "";
          }
        });
      }
    }

    // Restore note
    this.note = data.notas || "";
    const noteEl = document.getElementById(`inst-note-${sk}`);
    if (noteEl) noteEl.value = this.note;

    this._collectActions();
    if (this._el) this._el.classList.toggle("active", this.hasData);
  }
}


/* ═══════════════════════════════════════════════════════════════
   CLASS: PartCard — manages all instances for one part
   ═══════════════════════════════════════════════════════════════ */
class PartCard {
  constructor(category, partName, onUpdate) {
    this.category  = category;
    this.partName  = partName;
    this.baseKey   = `${category}::${partName}`;
    this.onUpdate  = onUpdate;
    this.instances = new Map(); // instanceKey -> PartInstance
    this._counter  = 0;
    this._el       = null;
    this._instsCt  = null;
  }

  render(container) {
    const safeBase = this.baseKey.replace(/[^a-zA-Z0-9]/g, "_");
    this._el = document.createElement("div");
    this._el.className = "part-card";
    this._el.id = `card-${safeBase}`;
    this._el.innerHTML = `
      <div class="part-card-head">
        <span class="part-card-name" title="${this.partName}">${this.partName}</span>
        <button class="btn-add-inst" id="add-inst-${safeBase}">
          <i class="fas fa-plus"></i> Agregar
        </button>
      </div>
      <div class="part-instances" id="insts-${safeBase}"></div>
    `;
    this._instsCt = this._el.querySelector(`#insts-${safeBase}`);
    this._el.querySelector(`#add-inst-${safeBase}`).addEventListener("click", () => this.addInstance());
    container.appendChild(this._el);
  }

  addInstance(presetKey) {
    const idx = this._counter++;
    const instanceKey = presetKey || `${this.baseKey}#${idx}`;
    const inst = new PartInstance(
      instanceKey, this.partName, this.category,
      () => { this._refreshCard(); this.onUpdate(); },
      (k) => { this.instances.delete(k); this._refreshCard(); this.onUpdate(); },
    );
    this.instances.set(instanceKey, inst);
    inst.render(this._instsCt);
    this._refreshCard();
    return inst;
  }

  _refreshCard() {
    if (!this._el) return;
    const active = [...this.instances.values()].some(i => i.hasData);
    this._el.classList.toggle("has-instances", this.instances.size > 0);
    if (this.instances.size > 0 && active) {
      this._el.querySelector(".part-card-head").style.borderBottom = "1px solid var(--border)";
    }
    // Highlight active instances
    this.instances.forEach(inst => {
      if (inst._el) inst._el.classList.toggle("active", inst.hasData);
    });
  }

  toJSON() {
    const out = {};
    this.instances.forEach((inst, key) => {
      if (inst.hasData || inst.sideSelector.active.size > 0) {
        out[key] = inst.toJSON();
      }
    });
    return out;
  }

  hide() { if (this._el) this._el.classList.add("hidden"); }
  show() { if (this._el) this._el.classList.remove("hidden"); }
}


/* ═══════════════════════════════════════════════════════════════
   CLASS: AutoSaver — debounced draft persistence
   ═══════════════════════════════════════════════════════════════ */
class AutoSaver {
  constructor(getPayload) {
    this._getPayload = getPayload;
    this._timer      = null;
    this._pill       = document.getElementById("autosave-pill");
    this._dot        = document.getElementById("autosave-dot");
    this._txt        = document.getElementById("autosave-txt");
  }

  schedule() {
    clearTimeout(this._timer);
    this._set("saving", "Guardando...");
    this._timer = setTimeout(() => this._save(), 1500);
  }

  async _save() {
    const payload = this._getPayload();
    if (!payload.uid || payload.uid === "TEST_ID") {
      this._set("saved", "Listo");
      return;
    }
    try {
      const res = await fetch("save_draft.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (res.ok) this._set("saved", "Guardado");
      else this._set("error", "Error");
    } catch {
      this._set("error", "Sin conexión");
    }
  }

  _set(state, label) {
    if (!this._pill) return;
    this._pill.className = `autosave-pill ${state}`;
    if (this._txt) this._txt.textContent = label;
  }
}


/* ═══════════════════════════════════════════════════════════════
   CLASS: SummaryPanel — live right panel
   ═══════════════════════════════════════════════════════════════ */
class SummaryPanel {
  constructor() {
    this._body  = document.getElementById("panel-body");
    this._count = document.getElementById("panel-count");
  }

  update(cards) {
    const items = [];
    cards.forEach(card => {
      card.instances.forEach(inst => {
        if (inst.hasData || inst.sideSelector.active.size > 0) {
          items.push({ inst, card });
        }
      });
    });

    if (this._count) this._count.textContent = items.length;
    if (!this._body) return;

    if (items.length === 0) {
      this._body.innerHTML = `<div class="panel-empty"><i class="fas fa-car"></i>No hay piezas seleccionadas</div>`;
      return;
    }

    this._body.innerHTML = items.map(({ inst, card }) => {
      inst._collectActions && inst._collectActions();
      const badgesHtml = inst.actions.map(a =>
        `<span class="action-badge ab-${a.accion}">${ACTIONS[a.accion] || a.accion}</span>`
      ).join("");
      const side = inst.sideSelector.label;
      return `
        <div class="sum-item">
          <div class="sum-item-name">${inst.partName}</div>
          <div class="sum-item-meta">
            <span class="sum-cat">${card.category}</span>
            ${side !== "Seleccionar lados" ? `<span class="sum-cat" style="color:var(--blue)">${side}</span>` : ""}
            ${badgesHtml}
          </div>
        </div>`;
    }).join("");
  }
}


/* ═══════════════════════════════════════════════════════════════
   CLASS: InspectionApp — main controller
   ═══════════════════════════════════════════════════════════════ */
class InspectionApp {
  constructor() {
    this.uid     = "";
    this.cards   = new Map();
    this.panel   = new SummaryPanel();
    this.saver   = new AutoSaver(() => this._draftPayload());
    this._notesModal       = document.getElementById("notes-modal");
    this._confirmModal     = document.getElementById("confirm-modal");
    this._successModal     = document.getElementById("success-modal");
    this._aseguradoraModal = document.getElementById("aseguradora-modal");
    this.fotos             = [];
    this.existingFotos     = [];
    this.fotoNotes         = {}; // { "filename_or_url": "nota" }
    this.aseguradora       = null; // Valor seleccionado: Allianz | HDI | Mapfre | Personal
  }

  async init() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");

    if (id) {
      this.uid = id;
      document.getElementById("uid").value = id;
      await this._loadVehicle(id);
    } else {
      this.uid = "TEST_ID";
      document.getElementById("uid").value = "TEST_ID";
    }

    this._buildCatNav();
    this._renderParts();

    if (id) await this._loadDraft(id);

    // Photo upload bindings
    const btnPhoto = document.getElementById("btn-capture-photo");
    const photoInput = document.getElementById("photo-input");
    if (btnPhoto && photoInput) {
      btnPhoto.addEventListener("click", () => photoInput.click());
      photoInput.addEventListener("change", (e) => this._handlePhotoSelection(e));
    }
    document.getElementById("btn-close-preview")?.addEventListener("click", () => {
      document.getElementById("photo-previews-container").style.display = "none";
      this._updateTopbarHeight();
    });

    this._bindVehicleInputs();
    this._bindSearch();
    this._bindEscape();
    this._bindGlobalActions();
    this._updateVehicleSummary();
    this._updateTopbarHeight();
    // Re-measure if fonts shift layout
    window.addEventListener("load", () => this._updateTopbarHeight());
    window.addEventListener("resize", () => this._updateTopbarHeight());
  }

  // ── Vehicle data ─────────────────────────────────────────────
  async _loadVehicle(id) {
    try {
      const res = await fetch(`get_vehicle.php?id=${id}`);
      if (!res.ok) return;
      const data = await res.json();
      if (data.error) return;
      const v = typeof data.datos_vehiculo === "string"
        ? JSON.parse(data.datos_vehiculo) : data.datos_vehiculo;
      this._fillVehicle(v);
    } catch (e) {
      console.warn("No se pudo cargar el vehículo:", e);
    }
  }

  _fillVehicle(v) {
    const map = {
      "input-placa": v.placa, "input-marca": v.marca, "input-linea": v.linea,
      "input-modelo": v.modelo, "input-color": v.color, "input-vin": v.vin,
      "input-propietario": v.propietario, "input-cedula": v.cedula_propietario,
    };
    Object.entries(map).forEach(([id, val]) => {
      const el = document.getElementById(id);
      if (el && val) el.value = val;
    });
    // Auto-collapse fields once data loaded
    const fields = document.getElementById('vehicle-fields');
    const icon   = document.getElementById('toggle-vh-icon');
    if (fields) fields.classList.add('collapsed');
    if (icon)   icon.className = 'fas fa-chevron-down';
    this._updateVehicleSummary();
    this._updateTopbarHeight();
  }

  _bindVehicleInputs() {
    ["input-placa","input-marca","input-linea","input-modelo","input-color","input-vin","input-propietario","input-cedula"]
      .forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", () => this.saver.schedule());
      });
    document.getElementById("general-notes")?.addEventListener("input", () => this.saver.schedule());
  }

  // ── Parts rendering ───────────────────────────────────────────
  _buildCatNav() {
    const nav = document.getElementById("cat-nav");
    if (!nav) return;
    Object.keys(PARTS_DB).forEach((cat, i) => {
      const btn = document.createElement("button");
      btn.className = `cat-pill${i === 0 ? " active" : ""}`;
      btn.textContent = cat;
      btn.addEventListener("click", () => {
        nav.querySelectorAll(".cat-pill").forEach(p => p.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(`section-${this._safeCat(cat)}`)
          ?.scrollIntoView({ behavior: "smooth", block: "start" });
      });
      nav.appendChild(btn);
    });
  }

  _safeCat(cat) { return cat.replace(/[^a-zA-Z0-9]/g, "_"); }

  _renderParts() {
    const root = document.getElementById("sections-root");
    Object.entries(PARTS_DB).forEach(([category, parts]) => {
      const sec = document.createElement("section");
      sec.className = "cat-section";
      sec.id = `section-${this._safeCat(category)}`;
      sec.innerHTML = `<div class="cat-title">${category}</div><div class="parts-grid" id="grid-${this._safeCat(category)}"></div>`;
      root.appendChild(sec);

      const grid = document.getElementById(`grid-${this._safeCat(category)}`);
      parts.forEach(part => {
        const card = new PartCard(category, part, () => this._onUpdate());
        this.cards.set(`${category}::${part}`, card);
        card.render(grid);
      });
    });
  }

  _handlePhotoSelection(e) {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;

    files.forEach(file => {
      if (!this.fotos.some(f => f.name === file.name && f.size === file.size)) {
        this.fotos.push(file);
      }
    });

    e.target.value = "";
    this._renderPhotoPreviews();
  }

  _renderPhotoPreviews() {
    const container = document.getElementById("photo-previews-container");
    const grid = document.getElementById("photo-grid");
    const countEl = document.getElementById("photo-count");
    if (!container || !grid || !countEl) return;

    const totalCount = this.fotos.length + this.existingFotos.length;
    countEl.textContent = totalCount;

    if (totalCount === 0) {
      container.style.display = "none";
      grid.innerHTML = "";
      this._updateTopbarHeight();
      return;
    }

    container.style.display = "block";
    grid.innerHTML = "";

    // 1. Renderizar fotos ya existentes en Cloudinary
    this.existingFotos.forEach((url, index) => {
      const item = document.createElement("div");
      item.className = "photo-preview-item existing-photo";

      const img = document.createElement("img");
      img.src = url;

      const btnRemove = document.createElement("button");
      btnRemove.className = "btn-remove-photo";
      btnRemove.innerHTML = "×";
      btnRemove.title = "Eliminar foto";
      btnRemove.addEventListener("click", () => this._removeExistingPhoto(index));

      // Badge indicador de que está en Cloudinary
      const badge = document.createElement("span");
      badge.className = "photo-badge-cloud";
      badge.innerHTML = '<i class="fas fa-cloud"></i>';
      badge.style.position = "absolute";
      badge.style.top = "4px";
      badge.style.left = "4px";
      badge.style.background = "rgba(40, 167, 69, 0.85)";
      badge.style.color = "white";
      badge.style.borderRadius = "3px";
      badge.style.padding = "2px 4px";
      badge.style.fontSize = "0.55rem";
      badge.style.pointerEvents = "none";

      const btnNote = document.createElement("button");
      btnNote.className = "btn-note-photo" + (this.fotoNotes[url] ? " has-note" : "");
      btnNote.innerHTML = '<i class="fas fa-comment-dots"></i>';
      btnNote.title = "Agregar nota";
      btnNote.addEventListener("click", () => this._openPhotoNoteModal(url));

      item.appendChild(img);
      item.appendChild(badge);
      item.appendChild(btnRemove);
      item.appendChild(btnNote);
      grid.appendChild(item);
    });

    // 2. Renderizar fotos nuevas capturadas localmente
    this.fotos.forEach((file, index) => {
      const item = document.createElement("div");
      item.className = "photo-preview-item new-photo";

      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      img.onload = () => URL.revokeObjectURL(img.src);

      const btnRemove = document.createElement("button");
      btnRemove.className = "btn-remove-photo";
      btnRemove.innerHTML = "×";
      btnRemove.addEventListener("click", () => this._removePhoto(index));

      const photoId = file.name;
      const btnNote = document.createElement("button");
      btnNote.className = "btn-note-photo" + (this.fotoNotes[photoId] ? " has-note" : "");
      btnNote.innerHTML = '<i class="fas fa-comment-dots"></i>';
      btnNote.title = "Agregar nota";
      btnNote.addEventListener("click", () => this._openPhotoNoteModal(photoId));

      item.appendChild(img);
      item.appendChild(btnRemove);
      item.appendChild(btnNote);
      grid.appendChild(item);
    });

    this._updateTopbarHeight();
  }

  _removePhoto(index) {
    this.fotos.splice(index, 1);
    this._renderPhotoPreviews();
    this.saver.schedule();
  }

  _removeExistingPhoto(index) {
    this.existingFotos.splice(index, 1);
    this._renderPhotoPreviews();
    this.saver.schedule();
  }

  _openPhotoNoteModal(photoId) {
    const modal = document.getElementById("photo-note-modal");
    const idInput = document.getElementById("photo-note-id");
    const textarea = document.getElementById("photo-note-textarea");
    if (!modal || !idInput || !textarea) return;

    idInput.value = photoId;
    textarea.value = this.fotoNotes[photoId] || "";
    modal.classList.add("open");
  }

  _closePhotoNoteModal() {
    const modal = document.getElementById("photo-note-modal");
    if (modal) modal.classList.remove("open");
  }

  _onUpdate(skipSave = false) {
    this.panel.update(this.cards);
    if (!skipSave) this.saver.schedule();
    // Update mobile badge count
    let total = 0;
    this.cards.forEach(card => {
      card.instances.forEach(inst => {
        if (inst.hasData || inst.sideSelector.active.size > 0) total++;
      });
    });
    const mc = document.getElementById("mobile-count");
    if (mc) mc.textContent = total;
  }

  // ── Search ────────────────────────────────────────────────────
  _bindSearch() {
    const input = document.getElementById("search-input");
    if (!input) return;
    input.addEventListener("input", () => {
      const q = input.value.toLowerCase().trim();
      this.cards.forEach((card, baseKey) => {
        if (!q || card.partName.toLowerCase().includes(q)) card.show();
        else card.hide();
      });
    });
  }

  // ── Draft ─────────────────────────────────────────────────────
  async _loadDraft(id) {
    try {
      const res = await fetch(`get_draft.php?id=${id}`).catch(() => null);
      if (!res || !res.ok) return;
      const text = await res.text();
      if (!text.trim()) return;
      let data = JSON.parse(text);
      if (Array.isArray(data)) data = data[0] || {};
      let piezas = data.piezas || data.datos_json;
      if (!piezas) return;
      if (typeof piezas === "string") piezas = JSON.parse(piezas);

      // Cargar fotos existentes del borrador
      let loadedFotos = [];
      if (data.urls_fotos && Array.isArray(data.urls_fotos)) {
        loadedFotos = data.urls_fotos.filter(Boolean);
      } else if (piezas && piezas.__urls_fotos__) {
        loadedFotos = piezas.__urls_fotos__;
        delete piezas.__urls_fotos__;
      }
      
      this.existingFotos = [];
      loadedFotos.forEach(f => {
        if (typeof f === "object" && f.url) {
          this.existingFotos.push(f.url);
          if (f.nota) this.fotoNotes[f.url] = f.nota;
        } else if (typeof f === "string") {
          this.existingFotos.push(f);
        }
      });
      this._renderPhotoPreviews();

      if (data.observaciones) {
        const el = document.getElementById("general-notes");
        if (el) el.value = data.observaciones;
      }

      Object.entries(piezas).forEach(([savedKey, info]) => {
        const baseKey = savedKey.includes("#") ? savedKey.split("#")[0] : savedKey;
        const card = this.cards.get(baseKey);
        if (!card) return;
        const inst = card.addInstance(savedKey);
        inst.restoreFrom(info);
      });

      this._onUpdate(true);
      this._toast("Borrador cargado", "success");
    } catch (e) {
      console.warn("Error cargando borrador:", e);
    }
  }

  _draftPayload() {
    const piezas = {};
    this.cards.forEach(card => {
      Object.assign(piezas, card.toJSON());
    });
    if (this.existingFotos && this.existingFotos.length > 0) {
      piezas.__urls_fotos__ = this.existingFotos.map(url => ({
        url: url,
        nota: this.fotoNotes[url] || ""
      }));
    }
    return {
      uid: this.uid,
      piezas,
      observaciones: document.getElementById("general-notes")?.value || "",
    };
  }

  // ── Notes modal ───────────────────────────────────────────────
  toggleNotes() {
    if (!this._notesModal) return;
    this._notesModal.classList.toggle("open");
  }

  // ── Aseguradora modal ───────────────────────────────────
  openAseguradoraModal() {
    // Verificar que haya al menos una pieza antes de abrir
    const items = [];
    this.cards.forEach(card => {
      card.instances.forEach(inst => {
        if (inst.hasData || inst.sideSelector.active.size > 0) items.push(inst);
      });
    });
    if (items.length === 0) {
      this._toast("Agrega al menos una pieza antes de enviar", "error");
      return;
    }

    // Restaurar estado visual de selección previa
    document.querySelectorAll(".aseg-btn").forEach(btn => {
      btn.classList.toggle("selected", btn.dataset.value === this.aseguradora);
    });
    const errEl = document.getElementById("aseg-error");
    if (errEl) errEl.style.display = "none";

    if (this._aseguradoraModal) this._aseguradoraModal.classList.add("open");
  }

  closeAseguradoraModal() {
    if (this._aseguradoraModal) this._aseguradoraModal.classList.remove("open");
  }

  // ── Confirm & send ────────────────────────────────────────────
  openConfirm() {
    const items = [];
    this.cards.forEach(card => {
      card.instances.forEach(inst => {
        if (inst.hasData || inst.sideSelector.active.size > 0) items.push(inst);
      });
    });

    if (items.length === 0) {
      this._toast("Agrega al menos una pieza antes de enviar", "error");
      return;
    }

    const list = document.getElementById("confirm-list");
    const obs  = document.getElementById("confirm-obs");
    if (list) {
      list.innerHTML = items.map(inst => {
        const acts = inst.actions.map(a => ACTIONS[a.accion]).filter(Boolean).join(", ");
        return `
          <div class="confirm-item">
            <strong>${inst.partName}</strong>
            <span>${inst.sideSelector.label}${acts ? " · " + acts : ""}</span>
          </div>`;
      }).join("");
    }
    if (obs) obs.textContent = document.getElementById("general-notes")?.value || "Sin observaciones";
    if (this._confirmModal) this._confirmModal.classList.add("open");
  }

  closeConfirm() {
    if (this._confirmModal) this._confirmModal.classList.remove("open");
  }

  openSuccessModal() {
    if (this._successModal) this._successModal.classList.add("open");
  }

  async submitFinal() {
    this.closeConfirm();
    this._showSendStatusWidget("sending");

    const piezas = {};
    this.cards.forEach(card => { Object.assign(piezas, card.toJSON()); });

    const existingFotosWithNotes = this.existingFotos.map(url => ({
      url: url,
      nota: this.fotoNotes[url] || ""
    }));

    const payload = {
      uid: this.uid,
      piezas,
      total_piezas: Object.keys(piezas).length,
      observaciones: document.getElementById("general-notes")?.value || "",
      urls_fotos: existingFotosWithNotes,
      aseguradora: this.aseguradora,
      kilometraje: this.kilometraje,
      ubicacion: this.ubicacion,
      datos_vehiculo: {
        placa:              document.getElementById("input-placa")?.value.trim(),
        marca:              document.getElementById("input-marca")?.value.trim(),
        linea:              document.getElementById("input-linea")?.value.trim(),
        modelo:             document.getElementById("input-modelo")?.value.trim(),
        color:              document.getElementById("input-color")?.value.trim(),
        vin:                document.getElementById("input-vin")?.value.trim(),
        propietario:        document.getElementById("input-propietario")?.value.trim(),
        cedula_propietario: document.getElementById("input-cedula")?.value.trim(),
      },
    };

    const formData = new FormData();
    formData.append("datos", JSON.stringify({ 
      ...payload, 
      aseguradora: this.aseguradora || "",
      kilometraje: this.kilometraje || "",
      ubicacion: this.ubicacion || ""
    }));
    this.fotos.forEach(file => {
      formData.append("fotos[]", file);
      formData.append("notas_fotos_nuevas[]", this.fotoNotes[file.name] || "");
    });

    try {
      const res = await fetch("send_inspection.php", {
        method: "POST",
        body: formData,
      });

      if (res.ok) {
        this._showSendStatusWidget("success");
      } else {
        const errData = await res.json().catch(() => ({}));
        const errMsg = errData.error || "Error al enviar la inspección.";
        this._showSendStatusWidget("error", errMsg);
      }
    } catch (err) {
      this._showSendStatusWidget("error", "Error de red. Revisa tu conexión.");
    }
  }

  _showSendStatusWidget(state, extraInfo = "") {
    const widget = document.getElementById("send-status-widget");
    const iconContainer = document.getElementById("sw-icon");
    const titleEl = document.getElementById("sw-title");
    const descEl = document.getElementById("sw-desc");
    const btn = document.getElementById("sw-action-btn");
    const closeBtn = document.getElementById("sw-close-btn");

    if (!widget || !iconContainer || !titleEl || !descEl || !btn) return;

    widget.className = "send-status-widget open " + state;

    const placa = document.getElementById("input-placa")?.value.trim() || "—";
    const aseguradora = this.aseguradora || "N/A";

    if (state === "sending") {
      iconContainer.innerHTML = '<i class="fas fa-paper-plane sw-sending-icon"></i>';
      titleEl.textContent = "Enviando Inspección...";
      descEl.innerHTML = `<strong>Placa:</strong> ${placa} &nbsp;·&nbsp; <strong>Aseguradora:</strong> ${aseguradora}`;
      btn.style.display = "none";
    } 
    else if (state === "success") {
      iconContainer.innerHTML = '<i class="fas fa-check"></i>';
      titleEl.textContent = "¡Enviado con Éxito!";
      descEl.innerHTML = `<strong>${placa} (${aseguradora})</strong> — Reporte enviado a Telegram.`;
      // Se oculta el botón "Nueva" a petición del usuario
      btn.style.display = "none";
    } 
    else if (state === "error") {
      iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
      titleEl.textContent = "Fallo al Enviar";
      descEl.textContent = extraInfo || "Error de comunicación. Intenta nuevamente.";
      btn.textContent = "Reintentar";
      btn.style.display = "block";
      btn.onclick = () => this.submitFinal();
    }

    if (closeBtn) {
      closeBtn.onclick = () => {
        widget.classList.remove("open");
      };
    }
  }

  // ── Misc ──────────────────────────────────────────────────────
  _bindEscape() {
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        document.querySelectorAll(".modal-overlay.open").forEach(m => m.classList.remove("open"));
        this._closeDrawer();
        if (this._successModal?.classList.contains("open")) {
          location.reload();
        }
      }
    });
  }

  // ── Collapsible vehicle fields ────────────────────────────────
  _bindVehicleToggle() {
    const btn   = document.getElementById("btn-toggle-vh");
    const fields = document.getElementById("vehicle-fields");
    const icon  = document.getElementById("toggle-vh-icon");
    if (!btn || !fields) return;

    btn.addEventListener("click", () => {
      const isNowCollapsed = fields.classList.toggle("collapsed");
      icon.className = isNowCollapsed ? "fas fa-chevron-down" : "fas fa-chevron-up";
      this._updateTopbarHeight();
    });
  }

  // ── Update topbar height CSS var ──────────────────────────────
  _updateTopbarHeight() {
    requestAnimationFrame(() => {
      const topbar = document.querySelector(".topbar");
      if (topbar) {
        const h = topbar.offsetHeight;
        document.documentElement.style.setProperty("--topbar-h", h + "px");
      }
    });
  }

  // ── Vehicle summary in topbar ─────────────────────────────────
  _updateVehicleSummary() {
    const placa = document.getElementById("input-placa")?.value.trim() || "—";
    const marca = document.getElementById("input-marca")?.value.trim() || "";
    const linea = document.getElementById("input-linea")?.value.trim() || "";
    const modelo = document.getElementById("input-modelo")?.value.trim() || "";
    const desc = [marca, linea, modelo].filter(Boolean).join(" ") || "Sin vehículo";
    const vsPlaca = document.getElementById("vs-placa");
    const vsDesc  = document.getElementById("vs-desc");
    if (vsPlaca) vsPlaca.textContent = placa;
    if (vsDesc)  vsDesc.textContent  = desc;
    if (placa !== "—") document.title = `Inspección ${placa}`;
  }

  // ── Drawer (mobile panel) ─────────────────────────────────────
  _openDrawer() {
    document.getElementById("drawer")?.classList.add("open");
    document.getElementById("drawer-overlay")?.classList.add("open");
    document.body.classList.add("drawer-open");
    // sync drawer body with panel body
    const panelBody = document.getElementById("panel-body");
    const drawerBody = document.getElementById("drawer-body");
    if (panelBody && drawerBody) drawerBody.innerHTML = panelBody.innerHTML;
  }

  _closeDrawer() {
    document.getElementById("drawer")?.classList.remove("open");
    document.getElementById("drawer-overlay")?.classList.remove("open");
    document.body.classList.remove("drawer-open");
  }

  _bindGlobalActions() {
    // NOTE: focusin/focusout handlers for 'modal-open' class removed — they were part of
    // the FAB visibility feature that was eliminated in commit 361ebfa.
    // The 'modal-open' class is not referenced in CSS.

    // Notes
    document.getElementById("btn-open-notes")?.addEventListener("click", () => this.toggleNotes());
    document.getElementById("close-notes")?.addEventListener("click", () => this.toggleNotes());
    document.getElementById("btn-save-notes")?.addEventListener("click", () => this.toggleNotes());

    // Confirm modal
    document.getElementById("close-confirm")?.addEventListener("click", () => this.closeConfirm());
    document.getElementById("btn-cancel-confirm")?.addEventListener("click", () => this.closeConfirm());
    document.getElementById("btn-confirm-send")?.addEventListener("click", () => this.submitFinal());

    // Success modal
    document.getElementById("btn-close-success")?.addEventListener("click", () => {
      if (this._successModal) this._successModal.classList.remove("open");
      location.reload();
    });

    // Send buttons (panel, drawer, topbar) → pasan por el modal de aseguradora
    document.getElementById("btn-send")?.addEventListener("click", () => this.openAseguradoraModal());
    document.getElementById("btn-topbar-send")?.addEventListener("click", () => this.openAseguradoraModal());
    document.getElementById("btn-send-drawer")?.addEventListener("click", () => { this._closeDrawer(); this.openAseguradoraModal(); });

    // Botones del modal de aseguradora
    document.querySelectorAll(".aseg-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        this.aseguradora = btn.dataset.value;
        document.querySelectorAll(".aseg-btn").forEach(b => b.classList.remove("selected"));
        btn.classList.add("selected");
        const errEl = document.getElementById("aseg-error");
        if (errEl) errEl.style.display = "none";
      });
    });

    // Botones del modal de ubicación
    document.querySelectorAll(".ubic-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        this.ubicacion = btn.dataset.value;
        document.querySelectorAll(".ubic-btn").forEach(b => b.classList.remove("selected"));
        btn.classList.add("selected");
        const errEl = document.getElementById("aseg-error");
        if (errEl) errEl.style.display = "none";
      });
    });

    // Limpiar campos al abrir el modal (opcional, o dejarlos si ya se ingresaron)
    // Se maneja desde openAseguradoraModal normalmente, pero aquí capturamos:
    document.getElementById("btn-confirm-aseg")?.addEventListener("click", () => {
      const kmInput = document.getElementById("input-kilometraje")?.value.trim();
      
      if (!this.aseguradora || !this.ubicacion || !kmInput) {
        const errEl = document.getElementById("aseg-error");
        if (errEl) errEl.style.display = "flex";
        return;
      }
      
      this.kilometraje = kmInput;
      this.closeAseguradoraModal();
      this.openConfirm();
    });
    document.getElementById("btn-cancel-aseg")?.addEventListener("click", () => this.closeAseguradoraModal());
    document.getElementById("close-aseg")?.addEventListener("click", () => this.closeAseguradoraModal());

    // Photo Note Modal
    document.getElementById("close-photo-note")?.addEventListener("click", () => this._closePhotoNoteModal());
    document.getElementById("btn-cancel-photo-note")?.addEventListener("click", () => this._closePhotoNoteModal());
    document.getElementById("btn-save-photo-note")?.addEventListener("click", () => {
      const idInput = document.getElementById("photo-note-id");
      const textarea = document.getElementById("photo-note-textarea");
      if (idInput && textarea) {
        const val = textarea.value.trim();
        if (val) {
          this.fotoNotes[idInput.value] = val;
        } else {
          delete this.fotoNotes[idInput.value];
        }
        this._renderPhotoPreviews(); // re-render to show/hide badge
        this.saver.schedule();
      }
      this._closePhotoNoteModal();
    });

    // Mobile drawer
    document.getElementById("mobile-panel-btn")?.addEventListener("click", () => this._openDrawer());
    document.getElementById("drawer-overlay")?.addEventListener("click", () => this._closeDrawer());
    document.getElementById("close-drawer")?.addEventListener("click", () => this._closeDrawer());

    // Vehicle toggle
    this._bindVehicleToggle();

    // Update summary on vehicle input changes
    ["input-placa","input-marca","input-linea","input-modelo"].forEach(id => {
      document.getElementById(id)?.addEventListener("input", () => this._updateVehicleSummary());
    });

    // Search clear button
    const searchInput = document.getElementById("search-input");
    const searchClear = document.getElementById("search-clear");
    if (searchInput && searchClear) {
      searchInput.addEventListener("input", () => {
        searchClear.style.display = searchInput.value ? "block" : "none";
      });
      searchClear.addEventListener("click", () => {
        searchInput.value = "";
        searchClear.style.display = "none";
        searchInput.dispatchEvent(new Event("input"));
        searchInput.focus();
      });
    }
  }

  _toast(msg, type = "success") {
    const container = document.getElementById("toast-container");
    if (!container) return;
    const t = document.createElement("div");
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${type === "success" ? "check-circle" : "exclamation-circle"}"></i> ${msg}`;
    container.appendChild(t);
    setTimeout(() => t.remove(), 3500);
  }
}


/* ═══════════════════════════════════════════════════════════════
   BOOT
   ═══════════════════════════════════════════════════════════════ */
window.addEventListener("DOMContentLoaded", () => {
  window.app = new InspectionApp();
  app.init();
});
