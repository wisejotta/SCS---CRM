<script setup>

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const emit = defineEmits([
  'upload:doc',
])
</script>

<template>
  <VRow
    style="background-color: #eee;"
    class="ma-0 mt-4 mb-2"
  >
    <VCol
      cols="12"
      sm="6"
      style="background-color: #fff;"
    >
      <p>
        Please upload a copy of your most recent educational qualification
      </p>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      class="mb-2 text-center"
    >
      <div class="mt-4">
        <i> {{ props.name || 'Your Documents' }}</i>
        <br>
        <br>
        <VBtn
          color="primary"
          @click="$emit('upload:doc', props.name)"
        >
          <VIcon
            size="22"
            icon="tabler-cloud-upload"
          />
          {{ props.name ? ' Replace' : ' Upload' }}
        </VBtn>
      </div>
    </VCol>
  </VRow>
</template>