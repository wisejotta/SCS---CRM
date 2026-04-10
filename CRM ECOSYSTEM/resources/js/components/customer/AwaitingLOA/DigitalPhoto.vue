<script setup>
import photo from '@images/avatars/photo-specifications.png';

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
      <h3>What is it and why do I need it?</h3>
      <p>
        A standard photo in digital format according to the specifications by the CIC, like the one in your Passport. This will be used on your Visa.
      </p>
      <h3>Photo Specifications</h3>
      <p>
        A standard digital passport photo (in full colour) that will be used for your application.
      </p>
      <p>
        Clear, sharp and in focus against white/grey background
      </p>
      <p>
        Taken with a neutral facial expression
      </p>
      <p>
        An image of your current appearance, taken within the last six months
      </p>
      <img :src="photo" alt="photo">
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