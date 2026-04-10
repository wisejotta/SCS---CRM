<script setup>
const props = defineProps({
  retainer: {
    type: Object,
    required: true,
  },
})
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
        In order for our RCIC to represent you and assist you with your application, you are required to sign the agreement and pay the full amount of the retainer.
        <br><br>
        If you have not already, please sign the retainer agreement here.
      </p>
    </VCol>

    <VCol
      cols="12"
      sm="6"
      class="mb-2 text-center"
    >
      <div class="mt-4">
        <i>Signed at: {{ props.retainer.signed_at || 'N\A' }}</i>
        <br>
        <br>
        <VBtn
          :disabled="!props.retainer"
          color="primary"
          :href="props.retainer.retainer"
          target="_blank"
        >
          View retainer
        </VBtn>
      </div>
    </VCol>
  </VRow>
</template>