<script setup>
// import AppDateTimePicker from '@core/components/AppDateTimePicker.vue'

const date = ref('')
const breaks = ref([])
const currentPage = ref(1)
const totalPage = ref(1)

const loading = ref(true)

let interval = null
const fetchBreaks = (load = true) => {
  loading.value = load
  axios.post('/api/breaks', {
    date: date.value,
  }).then((r) => {
    breaks.value = r.data

    if(!interval) clearInterval(interval)
    interval = setInterval(() => {
      breaks.value.forEach((_break, index) => {
        if(_break.seconds !== null) {
          breaks.value[index].seconds += 1
          breaks.value[index].duration = convertStoMs(_break.seconds)
        }
      })
    }, 1000)
  })
  .catch(e => console.log(e))
  .then(() => loading.value = false)
}

watchEffect(fetchBreaks)

function convertStoMs(seconds) {
  let minutes = Math.floor(seconds / 60);
  let extraSeconds = seconds % 60;
  minutes = minutes < 10 ? "0" + minutes : minutes;
  extraSeconds = extraSeconds < 10 ? "0" + extraSeconds : extraSeconds;
  return `${minutes}:${extraSeconds}`
}

// setInterval(() => {
//   axios.post('/api/breaks', {
//     date: date.value,
//   }).then((r) => {
//     r.data.forEach(b => {

//     })
//   })
// }, 5000)


const colour = (type) => {
  const colours = {
    cigarettes: 'primary',
    lunch: '',
    toilet: 'success',
    meeting: 'info',
    training: 'warning',
  }
  return colours[type]
}

</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Breaks">
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <VRow>
              <vCol cols="12" md="4" sm="12">
                <div  class="demo-space-x">
                  <!-- <AppDateTimePicker
                    v-model="date"
                    label="Date"
                  /> -->
                </div>
              </vCol>
            </VRow>
          </VCardText>
          <VProgressLinear
            :active="loading"
            :indeterminate="loading"
            absolute
            bottom
            color="deep-purple accent-4" />
          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  AGENT NAME
                </th>
                <th scope="col">
                  TIME
                </th>
                <th scope="col">
                  TYPE
                </th>
                <th scope="col">
                  DURATION
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="_break in breaks"
                :key="_break.id"
                style="height: 3.75rem;"
              >
                <td>
                  <span class="text-base">{{ _break.name }}</span>
                </td>

                <td>
                  <span class="text-base">{{ _break.time }}</span>
                </td>

                <td>
                  <VChip
                    :color="_break.overtime ? 'error' : colour(_break.type)"
                  >
                    {{ _break.type }}
                  </VChip>
                </td>

                <td>
                  <span class="text-base">{{ _break.duration }}</span>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!breaks.length">
              <tr>
                <td
                  colspan="7"
                  class="text-center"
                  >
                  {{ loading ? 'Loading... Please wait' : 'No data available' }}
                </td>
              </tr>
            </tfoot>
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="5"
              :length="totalPage"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss">
  @use "@core-scss/template/pages/misc.scss";
</style>
