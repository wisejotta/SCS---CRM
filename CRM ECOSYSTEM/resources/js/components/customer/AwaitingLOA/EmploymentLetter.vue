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
      <h4>Please upload your letter of employment.</h4>
      <p>
        This should state that you either have leave or authorized absence from your employer in your home country, to travel in Canada.
        <br>
        You may also upload a job offer letter from a new employer for when you return to your home country.
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