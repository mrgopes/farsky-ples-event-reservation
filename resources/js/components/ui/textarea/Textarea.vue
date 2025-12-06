<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { cn } from '@/lib/utils'
import { marked } from 'marked'

interface Props {
  class?: string
  modelValue?: string
  showMarkdown?: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const textareaRef = ref<HTMLTextAreaElement | null>(null)
const showPreview = ref(false)

// History management for undo/redo
const history = ref<string[]>([])
const historyIndex = ref(-1)
const isUndoRedoing = ref(false)

const value = computed({
  get: () => props.modelValue,
  set: (newValue) => {
    if (!isUndoRedoing.value) {
      // Remove any future history when making a new change
      history.value = history.value.slice(0, historyIndex.value + 1)

      // Add new state to history
      history.value.push(newValue || '')
      historyIndex.value = history.value.length - 1

      // Limit history to 50 entries
      if (history.value.length > 50) {
        history.value.shift()
        historyIndex.value--
      }
    }
    emit('update:modelValue', newValue || '')
  }
})

// Render markdown preview
const renderedMarkdown = computed(() => {
  if (!props.modelValue) return ''
  return marked.parse(props.modelValue, { async: false }) as string
})

// Initialize history with the initial value
onMounted(() => {
  if (props.modelValue !== undefined) {
    history.value = [props.modelValue]
    historyIndex.value = 0
  }
})

// Handle keyboard shortcuts
const handleKeyDown = (event: KeyboardEvent) => {
  // Ctrl+Z or Cmd+Z (undo)
  if ((event.ctrlKey || event.metaKey) && event.key === 'z' && !event.shiftKey) {
    event.preventDefault()
    undo()
  }
  // Ctrl+Y or Cmd+Y or Ctrl+Shift+Z (redo)
  else if ((event.ctrlKey || event.metaKey) && (event.key === 'y' || (event.key === 'z' && event.shiftKey))) {
    event.preventDefault()
    redo()
  }
}

const undo = () => {
  if (historyIndex.value > 0) {
    historyIndex.value--
    isUndoRedoing.value = true
    emit('update:modelValue', history.value[historyIndex.value])
    setTimeout(() => {
      isUndoRedoing.value = false
    }, 0)
  }
}

const redo = () => {
  if (historyIndex.value < history.value.length - 1) {
    historyIndex.value++
    isUndoRedoing.value = true
    emit('update:modelValue', history.value[historyIndex.value])
    setTimeout(() => {
      isUndoRedoing.value = false
    }, 0)
  }
}

const insertMarkdown = (before: string, after: string = '') => {
  const textarea = textareaRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = value.value || ''
  const selectedText = text.substring(start, end)

  const newText = text.substring(0, start) + before + selectedText + after + text.substring(end)
  emit('update:modelValue', newText)

  // Set cursor position after inserted markdown
  setTimeout(() => {
    textarea.focus()
    const newPosition = start + before.length + selectedText.length
    textarea.setSelectionRange(newPosition, newPosition)
  }, 0)
}

const wrapSelection = (wrapper: string) => {
  insertMarkdown(wrapper, wrapper)
}

const insertAtCursor = (text: string) => {
  insertMarkdown(text, '')
}

const togglePreview = () => {
  showPreview.value = !showPreview.value
}

// Attach keyboard listener
onMounted(() => {
  const textarea = textareaRef.value
  if (textarea) {
    textarea.addEventListener('keydown', handleKeyDown)
  }
})

onBeforeUnmount(() => {
  const textarea = textareaRef.value
  if (textarea) {
    textarea.removeEventListener('keydown', handleKeyDown)
  }
})
</script>

<template>
  <div class="space-y-2">
    <div v-if="showMarkdown" class="flex flex-wrap gap-1 p-2 border border-input rounded-md bg-muted/30">
      <button
        type="button"
        @click="wrapSelection('**')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Bold"
      >
        <strong>B</strong>
      </button>
      <button
        type="button"
        @click="wrapSelection('*')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Italic"
      >
        <em>I</em>
      </button>
      <button
        type="button"
        @click="insertAtCursor('\n## ')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Heading"
      >
        H
      </button>
      <button
        type="button"
        @click="insertMarkdown('[', '](url)')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Link"
      >
        🔗
      </button>
      <button
        type="button"
        @click="insertAtCursor('\n- ')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Bullet list"
      >
        •
      </button>
      <button
        type="button"
        @click="insertAtCursor('\n1. ')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Numbered list"
      >
        1.
      </button>
      <button
        type="button"
        @click="wrapSelection('`')"
        class="px-2 py-1 text-sm font-mono hover:bg-muted rounded"
        title="Code"
      >
        &lt;/&gt;
      </button>
      <button
        type="button"
        @click="insertAtCursor('\n> ')"
        class="px-2 py-1 text-sm hover:bg-muted rounded"
        title="Quote"
      >
        "
      </button>
      <div class="flex-1"></div>
      <button
        type="button"
        @click="togglePreview"
        :class="cn(
          'px-2 py-1 text-sm rounded',
          showPreview ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
        )"
        title="Toggle preview"
      >
        👁
      </button>
      <button
        type="button"
        @click="undo"
        :disabled="historyIndex <= 0"
        class="px-2 py-1 text-sm hover:bg-muted rounded disabled:opacity-30 disabled:cursor-not-allowed"
        title="Undo (Ctrl+Z)"
      >
        ↶
      </button>
      <button
        type="button"
        @click="redo"
        :disabled="historyIndex >= history.length - 1"
        class="px-2 py-1 text-sm hover:bg-muted rounded disabled:opacity-30 disabled:cursor-not-allowed"
        title="Redo (Ctrl+Y)"
      >
        ↷
      </button>
    </div>
    <div :class="showPreview ? 'grid grid-cols-2 gap-4' : ''">
      <textarea
        ref="textareaRef"
        v-model="value"
        :class="cn(
          'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
          props.class,
          showPreview ? 'min-h-[300px]' : ''
        )"
      />
      <div
        v-if="showPreview"
        class="flex min-h-[300px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm overflow-auto"
      >
        <div v-html="renderedMarkdown" class="markdown-content w-full"></div>
      </div>
    </div>
  </div>
</template>
