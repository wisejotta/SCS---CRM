<script setup>
import { ref } from 'vue';

const isDialogVisible = ref(false)

const departments = ref([])
const payment = ref({})
let interval = null
let latest = 0
const fetchTv = () => {
  axios
    .get('/api/tv-data')
    .then((r) => {
      departments.value = r.data.departments
      latest = r.data.latest
      if(interval === null) {
        interval = setInterval(() => {
          axios
            .get(`/api/tv-last-payment/${latest}`)
            .then(r => {
              fetchTv()
              isDialogVisible.value = true
              payment.value = r.data
              setTimeout(() => {
                isDialogVisible.value = false
              }, 10000)
            })
        }, 5000)
      }
    })
    .catch(e => { console.log(e) })
}

fetchTv()

const screenHeight = ref(screen.height)
const height = ref(screenHeight.value - 260);

const fullscreen = ref(false)
/* Get the documentElement (<html>) to display the page in fullscreen */
const elem = document.documentElement;

/* View in fullscreen */
function openFullscreen() {
  fullscreen.value = true
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.webkitRequestFullscreen) { /* Safari */
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) { /* IE11 */
    elem.msRequestFullscreen();
  }
  height.value = screenHeight.value - 180;
}

/* Close fullscreen */
function closeFullscreen() {
  fullscreen.value = false
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) { /* Safari */
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) { /* IE11 */
    document.msExitFullscreen();
  }
  height.value = screenHeight.value - 260;
}

</script>

<template>
  <div
    class="pa-4"
    style="background-repeat: repeat;"
    :style="isDialogVisible ? { 'background-image': 'url(/img/4A5.gif)', 'height': screenHeight + 'px' } : null"
  >
    <VDialog
      v-model="isDialogVisible"
      width="500"
    >
      <VCard
        style="background-image: url('/img/sbe-sbenv.gif'); background-repeat: repeat;"
      >
        <VCardText>
          <div style="width: 100%;">
            <div class="d-flex justify-center">
              <h5 class="text-h3 font-weight-medium">
                {{ payment.name }}
              </h5>
            </div>
            <h3 class="text-h1 my-3 d-flex justify-center" style="color: #5cb731;">
              {{ payment.amount }}
            </h3>
          </div>
        </VCardText>
      </VCard>
    </VDialog>

    <VRow
      v-show="isDialogVisible == false"
    >
      <VCol
        cols="12"
        sm="6"
        class="me-0 pa-0"
        v-for="(department, i) in departments"
      >
        <VCard
          :height="height"
          style="overflow-y: scroll;background: transparent"
          flat
        >

          <VCardText>
            <main>
              <div id="header">
                <h1>{{ department.name }}</h1>
                <VBtn
                  icon
                  color="default"
                  variant="plain"
                  class="share" 
                  v-if="i == 7" 
                  @click="fullscreen ? closeFullscreen() : openFullscreen()"
                >
                  <VIcon
                    size="22"
                    :icon="fullscreen ? 'tabler-arrows-minimize' : 'tabler-maximize'"
                  />
                </VBtn>
              </div>
              <div id="leaderboard">
                <div class="ribbon"></div>
                <table>
                  <tr v-for="(agent, j) in department.agents" :key="j">
                    <td class="number">{{ j + 1 }}</td>
                    <td class="name">{{ agent.name }}</td>
                    <td>
                      <VProgressCircular
                        :size="42"
                        :width="3"
                        :model-value="agent.progress"
                        :color="j == 0 ? 'white' : '#5c5be5'"
                        class="me-2"
                        :style="{ 'font-size': agent.progress <= 100 ? '14px': '12px' }"
                      >
                      <strong>{{ agent.progress }}</strong>
                    </VProgressCircular>
                    </td>
                    <td class="points">
                      {{ department.name == 'FILE OPENING' ? agent.sales : agent.progress + ' %' }}
                      <img v-if="j == 0" class="gold-medal" src="https://github.com/malunaridev/Challenges-iCodeThis/blob/master/4-leaderboard/assets/gold-medal.png?raw=true" alt="gold medal"/>
                    </td>
                  </tr>
                </table>
              </div>
            </main>
          </VCardText>
        </VCard>

        <VCard class="pb-0 ml-4 mr-4 mt-1">
          <VCardText>
            <div class="d-flex align-center gap-2 mb-2 pb-1 flex-wrap">
              <h4 class="text-4xl font-weight-semibold">
                {{ department.count }}
              </h4>
            </div>
            <p class="text-sm">
              Sales
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";

* {
  font-size: 62, 5%;
  box-sizing: border-box;
  margin: 0;
}

body {
  height: 100%;
  width: 100%;
  min-height: 100vh;
  background-color: #fbfaff;
  align-items: center;
  justify-content: center;
}

main {
  width: 100%;
  height: 43rem;
  background-color: #ffffff;
  -webkit-box-shadow: 0px 5px 15px 8px #e4e7fb;
  box-shadow: 0px 5px 15px 8px #e4e7fb;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 0.5rem;
}

#header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2.5rem 2rem;
}

h1 {
  font-family: "Rubik", sans-serif;
  font-size: 1.7rem;
  color: #141a39;
  text-transform: uppercase;
  cursor: default;
}

#leaderboard {
  width: 100%;
  position: relative;
}

table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  color: #141a39;
  cursor: default;
}

tr {
  transition: all 0.2s ease-in-out;
  border-radius: 0.2rem;
}

tr:not(:first-child):hover {
  background-color: #fff;
  transform: scale(1.1);
  -webkit-box-shadow: 0px 5px 15px 8px #e4e7fb;
  box-shadow: 0px 5px 15px 8px #e4e7fb;
}

tr:nth-child(odd) {
  background-color: #f9f9f9;
}

tr:nth-child(1) {
  color: #fff;
}

td {
  height: 5rem;
  font-family: "Rubik", sans-serif;
  font-size: 1.4rem;
  padding: 1rem 2rem;
  position: relative;
}

.number {
  width: 1rem;
  font-size: 2.2rem;
  font-weight: bold;
  text-align: left;
}

.name {
  text-align: left;
  font-size: 1.2rem;
}

.points {
  font-weight: bold;
  font-size: 1.3rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.points:first-child {
  width: 10rem;
}

.gold-medal {
  height: 3rem;
  margin-left: 1.5rem;
}

.ribbon {
  width: 100%;
  height: 5.5rem;
  top: -0.5rem;
  background-color: #5c5be5;
  position: absolute;
  left: -1rem;
  -webkit-box-shadow: 0px 15px 11px -6px #7a7a7d;
  box-shadow: 0px 15px 11px -6px #7a7a7d;
}

.ribbon::before {
  content: "";
  height: 1.5rem;
  width: 1.5rem;
  bottom: -0.8rem;
  left: 0.35rem;
  transform: rotate(45deg);
  background-color: #5c5be5;
  position: absolute;
  z-index: -1;
}

.ribbon::after {
  content: "";
  height: 1.5rem;
  width: 1.5rem;
  bottom: -0.8rem;
  right: 0.35rem;
  transform: rotate(45deg);
  background-color: #5c5be5;
  position: absolute;
  z-index: -1;
}


@media (max-width: 740px) {
    * {
      font-size: 70%;
    }
}

@media (max-width: 500px) {
    * {
      font-size: 55%;
    }
}

@media (max-width: 390px) {
    * {
      font-size: 45%;
    }
}

</style>

<route lang="yaml">
meta:
  layout: blank
</route>
