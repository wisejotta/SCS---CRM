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
      <h4>What is it and why do I need it?</h4>
      <p>
        A letter of acceptance from the acedemic institution where you are going to study.
        <br>
        This is to show the CIC that you have been accepted and that you are going to study in Canada.
      </p>

      <h4>How do I get this?</h4>
      <p>
        The academic institution where you are going to study will send you this.
        <br><br>
        Simply upload the acceptance letter here.
      </p>

      <h4>What is a receipt of tuition or fees contributed?</h4>
      <p>An Official Tuition Receipt is a letter written by the institution you will attend as proof that you have either paid in full or it will show the amount you have contributed thus far.</p>

      <h4>How do I get this?</h4>
      <p>You can get this from the academic institution you are going to study at.</p>

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