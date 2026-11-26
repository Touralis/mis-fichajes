<script setup>
import { ref } from 'vue';
const emit=defineEmits(['change']);
const canvas=ref(null), drawing=ref(false), hasDrawing=ref(false);
function point(e){const r=canvas.value.getBoundingClientRect();return {x:(e.clientX-r.left)*canvas.value.width/r.width,y:(e.clientY-r.top)*canvas.value.height/r.height}}
function start(e){e.preventDefault();canvas.value.setPointerCapture(e.pointerId);drawing.value=true;const p=point(e),c=canvas.value.getContext('2d');c.strokeStyle='#182d28';c.lineWidth=3;c.lineCap='round';c.lineJoin='round';c.beginPath();c.moveTo(p.x,p.y)}
function move(e){if(!drawing.value)return;const p=point(e),c=canvas.value.getContext('2d');c.lineTo(p.x,p.y);c.stroke();hasDrawing.value=true;emit('change',true)}
function stop(){drawing.value=false}
function clear(){canvas.value.getContext('2d').clearRect(0,0,canvas.value.width,canvas.value.height);hasDrawing.value=false;emit('change',false)}
function getBlob(){return new Promise(resolve=>canvas.value.toBlob(resolve,'image/png'))}
defineExpose({clear,getBlob,hasDrawing});
</script>
<template><div class="signature-pad"><canvas ref="canvas" width="720" height="240" aria-label="Área para dibujar la firma" @pointerdown="start" @pointermove="move" @pointerup="stop" @pointercancel="stop"></canvas><button class="outline" type="button" @click="clear">Limpiar firma</button></div></template>
