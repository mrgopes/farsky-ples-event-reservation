<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
  rows?: number;
  cols?: number;
  seatSize?: number;
  seatGap?: number;
  modelValue?: number[];
  maxSelected?: number;
}>();

const emit = defineEmits(['update:modelValue']);

const seatRows = props.rows ?? 5;
const seatCols = props.cols ?? 10;
const seatSize = props.seatSize ?? 32;
const seatGap = props.seatGap ?? 8;
const selectedSeats = ref<number[]>(props.modelValue ? [...props.modelValue] : []);
const canvasRef = ref<HTMLCanvasElement | null>(null);

function seatIndex(row: number, col: number) {
  return row * seatCols + col;
}

function drawSeats() {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  // Detect dark mode
  const isDark = document.documentElement.classList.contains('dark');
  const seatColor = isDark ? '#444' : '#e5e7eb';
  const selectedColor = isDark ? '#2563eb' : '#2563eb';
  const borderColor = isDark ? '#bbb' : '#333';
  for (let row = 0; row < seatRows; row++) {
    for (let col = 0; col < seatCols; col++) {
      const x = col * (seatSize + seatGap) + seatGap;
      const y = row * (seatSize + seatGap) + seatGap;
      const idx = seatIndex(row, col);
      ctx.fillStyle = selectedSeats.value.includes(idx) ? selectedColor : seatColor;
      ctx.strokeStyle = borderColor;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(x + seatSize / 2, y + seatSize / 2, seatSize / 2, 0, 2 * Math.PI);
      ctx.fill();
      ctx.stroke();
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
        if (selectedSeats.value.includes(idx)) {
          selectedSeats.value = selectedSeats.value.filter((i: number) => i !== idx);
        } else {
          if (props.maxSelected && selectedSeats.value.length >= props.maxSelected) return;
          selectedSeats.value.push(idx);
        }
        emit('update:modelValue', [...selectedSeats.value]);
        drawSeats();
        return;
      }
    }
  }
}

onMounted(() => {
  drawSeats();
  if (canvasRef.value) {
    canvasRef.value.addEventListener('click', handleCanvasClick);
  }
});

watch(() => props.modelValue, (val) => {
  if (val) selectedSeats.value = [...val];
  drawSeats();
});
</script>

<template>
  <canvas
    ref="canvasRef"
    :width="seatCols * (seatSize + seatGap) + seatGap"
    :height="seatRows * (seatSize + seatGap) + seatGap"
    style="border: 1px solid #ccc; background: transparent; cursor: pointer;"
  ></canvas>
</template>
