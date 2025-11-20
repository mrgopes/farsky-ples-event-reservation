<script setup lang="ts">
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';

const props = defineProps<{
  // External template mode
  src?: string; // URL/path to an HTML/SVG file containing <circle> elements
  seatSelector?: string; // CSS selector for seat circles inside the external file
  // Canvas fallback mode (used if no src provided)
  rows?: number;
  cols?: number;
  seatSize?: number;
  seatGap?: number;
  modelValue?: number[];
  maxSelected?: number;
  // New: reserved seats that cannot be selected
  reservedSeats?: number[];
}>();

const emit = defineEmits(['update:modelValue']);

// Shared selection state
const selectedSeats = ref<number[]>(props.modelValue ? [...props.modelValue] : []);

// ===== External HTML/SVG mode =====
const htmlContent = ref<string>('');
const containerRef = ref<HTMLElement | null>(null);
let seatNodes: SVGCircleElement[] = [];
let seatIndices: number[] = [];
const seatCssSelector = props.seatSelector || 'circle.seat';

function isDarkMode() {
  return document.documentElement.classList.contains('dark');
}
function isReserved(idx: number): boolean {
  return Array.isArray(props.reservedSeats) && props.reservedSeats.includes(idx);
}
function reservedFill() {
  return isDarkMode() ? '#2a2a2a' : '#9ca3af';
}
function seatFill(selected: boolean, reserved: boolean) {
  if (reserved) return reservedFill();
  if (selected) return '#2563eb';
  return isDarkMode() ? '#444' : '#e5e7eb';
}
function seatStroke() {
  return isDarkMode() ? '#bbb' : '#333';
}

function reconcileExternalUI() {
  if (!seatNodes.length) return;
  const stroke = seatStroke();
  seatNodes.forEach((node, i) => {
    const idx = seatIndices[i] ?? i;
    const reserved = isReserved(idx);
    const sel = selectedSeats.value.includes(idx);
    node.setAttribute('fill', seatFill(sel, reserved));
    node.setAttribute('stroke', stroke);
    node.setAttribute('stroke-width', '2');
    (node as any).style.transition = 'fill 0.2s, opacity 0.2s';
    (node as any).style.cursor = reserved ? 'not-allowed' : 'pointer';
    (node as any).style.opacity = reserved ? '0.6' : '1';
    (node as any).style.pointerEvents = 'auto';
  });
}

function onSeatNodeClickFactory(idx: number) {
  return (e: Event) => {
    e.preventDefault();
    // Block interaction with reserved seats
    if (isReserved(idx)) return;
    if (selectedSeats.value.includes(idx)) {
      selectedSeats.value = selectedSeats.value.filter((i) => i !== idx);
    } else {
      const max = props.maxSelected ?? Infinity;
      if (selectedSeats.value.length >= max) return;
      selectedSeats.value = [...selectedSeats.value, idx];
    }
    emit('update:modelValue', [...selectedSeats.value]);
    reconcileExternalUI();
  };
}

function bindExternalHandlers() {
  const root = containerRef.value;
  if (!root) return;
  // Prefer provided selector, otherwise try some sensible defaults
  const selector = seatCssSelector || 'circle.seat, circle[data-seat], circle';
  seatNodes = Array.from(root.querySelectorAll(selector)) as SVGCircleElement[];
  // Compute indices from data-seat if provided, otherwise fallback to DOM order
  seatIndices = seatNodes.map((node, i) => {
    const raw = (node as unknown as { dataset?: Record<string, string> })?.dataset?.seat;
    const parsed = raw ? parseInt(raw, 10) : NaN;
    return Number.isFinite(parsed) ? parsed : i;
  });
  // Attach listeners and initial styles
  seatNodes.forEach((node, i) => {
    node.addEventListener('click', onSeatNodeClickFactory(seatIndices[i] ?? i));
  });
  reconcileExternalUI();
}

function unbindExternalHandlers() {
  const root = containerRef.value;
  if (!root || !seatNodes.length) return;
  seatNodes.forEach((node) => {
    node.replaceWith(node.cloneNode(true)); // quick way to drop listeners
  });
  seatNodes = [];
  seatIndices = [];
}

async function loadExternalTemplate() {
  if (!props.src) return;
  try {
    const res = await fetch(props.src);
    if (!res.ok) {
      console.error(`Failed to load template: ${res.status}`);
      return;
    }
    htmlContent.value = await res.text();
    await nextTick();
    bindExternalHandlers();
  } catch (e) {
    console.error(e);
  }
}

// Keep selection in sync with v-model
watch(() => props.modelValue, (val) => {
  if (Array.isArray(val)) {
    // Filter out any reserved seats from external selection value
    const filtered = val.filter((i) => !isReserved(i));
    if (filtered.length !== val.length) {
      selectedSeats.value = filtered;
      emit('update:modelValue', [...filtered]);
    } else {
      selectedSeats.value = [...val];
    }
    reconcileExternalUI();
    drawSeats(); // also update canvas if in fallback mode
  }
});

// React when reserved seats change: drop any newly-reserved selections
watch(() => props.reservedSeats, () => {
  const current = selectedSeats.value;
  const filtered = current.filter((i) => !isReserved(i));
  if (filtered.length !== current.length) {
    selectedSeats.value = filtered;
    emit('update:modelValue', [...filtered]);
  }
  reconcileExternalUI();
  drawSeats();
}, { deep: true });

// Reload external template if src or selector changes
watch(
  () => [props.src, props.seatSelector],
  async () => {
    if (props.src) {
      unbindExternalHandlers();
      htmlContent.value = '';
      await nextTick();
      await loadExternalTemplate();
    }
  }
);

onMounted(() => {
  if (props.src) {
    loadExternalTemplate();
  } else {
    // Fallback to canvas grid if no external template provided
    drawSeats();
    if (canvasRef.value) {
      canvasRef.value.addEventListener('click', handleCanvasClick);
    }
  }
});

onBeforeUnmount(() => {
  if (props.src) {
    unbindExternalHandlers();
  } else if (canvasRef.value) {
    canvasRef.value.removeEventListener('click', handleCanvasClick);
  }
});

// ===== Canvas fallback mode (existing behavior) =====
const seatRows = props.rows ?? 5;
const seatCols = props.cols ?? 10;
const seatSize = props.seatSize ?? 32;
const seatGap = props.seatGap ?? 8;
const canvasRef = ref<HTMLCanvasElement | null>(null);

function seatIndex(row: number, col: number) {
  return row * seatCols + col;
}

function drawSeats() {
  if (!canvasRef.value || props.src) return; // don't draw if using external template
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  const isDark = isDarkMode();
  const seatColor = isDark ? '#444' : '#e5e7eb';
  const selectedColor = '#2563eb';
  const disabledColor = reservedFill();
  const borderColor = isDark ? '#bbb' : '#333';
  for (let row = 0; row < seatRows; row++) {
    for (let col = 0; col < seatCols; col++) {
      const x = col * (seatSize + seatGap) + seatGap;
      const y = row * (seatSize + seatGap) + seatGap;
      const idx = seatIndex(row, col);
      const isRes = isReserved(idx);
      ctx.fillStyle = isRes
        ? disabledColor
        : (selectedSeats.value.includes(idx) ? selectedColor : seatColor);
      ctx.strokeStyle = borderColor;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(x + seatSize / 2, y + seatSize / 2, seatSize / 2, 0, 2 * Math.PI);
      ctx.fill();
      ctx.stroke();
      // Optional: visual slash for reserved to emphasize disabled
      // if (isRes) {
      //   ctx.strokeStyle = isDark ? '#888' : '#666';
      //   ctx.lineWidth = 2;
      //   ctx.beginPath();
      //   ctx.moveTo(x + 6, y + seatSize - 6);
      //   ctx.lineTo(x + seatSize - 6, y + 6);
      //   ctx.stroke();
      // }
    }
  }
}

function handleCanvasClick(e: MouseEvent) {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  for (let row = 0; row < seatRows; row++) {
    for (let col = 0; col < seatCols; col++) {
      const sx = col * (seatSize + seatGap) + seatGap;
      const sy = row * (seatSize + seatGap) + seatGap;
      const idx = seatIndex(row, col);
      const dx = x - (sx + seatSize / 2);
      const dy = y - (sy + seatSize / 2);
      if (dx * dx + dy * dy <= (seatSize / 2) * (seatSize / 2)) {
        // Ignore clicks on reserved seats
        if (isReserved(idx)) return;
        if (selectedSeats.value.includes(idx)) {
          selectedSeats.value = selectedSeats.value.filter((i: number) => i !== idx);
        } else {
          const max = props.maxSelected ?? Infinity;
          if (selectedSeats.value.length >= max) return;
          selectedSeats.value.push(idx);
        }
        emit('update:modelValue', [...selectedSeats.value]);
        drawSeats();
        return;
      }
    }
  }
}
</script>

<template>
  <div v-if="src" style="border: 1px solid #ccc; background: transparent; cursor: pointer;">
    <div ref="containerRef" v-html="htmlContent"></div>
  </div>
  <canvas
    v-else
    ref="canvasRef"
    :width="seatCols * (seatSize + seatGap) + seatGap"
    :height="seatRows * (seatSize + seatGap) + seatGap"
    style="border: 1px solid #ccc; background: transparent; cursor: pointer;"
  ></canvas>
</template>
