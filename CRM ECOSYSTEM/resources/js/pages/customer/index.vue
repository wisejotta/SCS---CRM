<script setup>
import UserProfile from './UserProfile.vue'
import canada from '@images/avatars/canadian-flag.png'
import job1 from '@images/job-1.jpg'
import job2 from '@images/job-2.jpg'
import job3 from '@images/job-3.jpg'
import job4 from '@images/job-4.jpg'
import job5 from '@images/job-5.jpg'
import job6 from '@images/job-6.jpg'
import job7 from '@images/job-7.jpg'

import { themeConfig } from '@themeConfig'
themeConfig.app.title = 'Visas Canada'
localStorage.setItem('lightThemePrimaryColor', '#cf2e2e')

const openedPanel = ref(null)
const loading = ref(true)
const user = JSON.parse(localStorage.getItem('user'))
const customer = ref({
  title: `${user.name} ${user.surname}`,
})

axios.get('/api/customers/customer').then((r) => {
  loading.value = false
  customer.value = r.data
})

const viewJobs = (url) => {
  window.open(url, '_blank')
}

</script>

<template>  
  <div class="pa-4">
    <VCard class="mb-4">
      <div class="demo-space-x pa-3">
        <h5 class="text-h5">
          {{ customer.title }}
        </h5>
        <VSpacer />
        <UserProfile />
      </div>
    </VCard>

    <VProgressLinear
      :active="loading"
      :indeterminate="loading"
      v-show="loading"
      absolute
      bottom
      color="deep-purple accent-4"
      class="mb-4"
    />

    <VCard
      class="mb-4"
      v-show="!loading"
    >
      <VCardText>
        <div class="demo-space-x">
          <VAvatar
            rounded="lg"
            color="primary"
            icon="tabler-plane-tilt"
            size="large"
          />
          <div>
            <h3>My Visa</h3>
            <div>Your active visa applications and status updates.</div>
          </div>
        </div>
      </VCardText>
    </VCard>

    <VCard
      class="mb-4"
      v-show="!loading"
    >
      <VCardText>
        <div class="demo-space-x">
          <VAvatar :image="canada" color="primary"/>
          <div>
            <h3>Canada</h3>
            <div>{{ customer.product }}</div>
          </div>

          <VSpacer />

          <VBtn
            variant="outlined"
            to="/customer/visa"
          >
            <VIcon
              start
              icon="tabler-pencil"
            />
            Edit
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Available Jobs -->
    <VCard title="Available Jobs" variant="tonal">
      <VCardText>
        <VExpansionPanels
          v-model="openedPanel"
          class="expansion-panels-width-border"
        >
          <!-- JOB BANK -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 0 ? 'primary' : ''"
            >
              JOB BANK
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow class="ma-0 mt-4 mb-2">
                <VCol
                  cols="12"
                  sm="6"
                >
                  <h3>What is it Job Bank?</h3>
                  <p>
                    Job Bank is Canada's national employment service, available as a website and mobile app. We help Canadians find work and plan their careers, and we make it easier for employers to recruit and hire across the country.
                  </p>
                  <VBtn variant="outlined" @click="viewJobs('https://www.jobbank.gc.ca/home')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job1" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>

          <!-- JOB OMAS -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 1 ? 'primary' : ''"
            >
              JOB OMAS
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow class="ma-0 mt-4">
                <VCol
                  cols="12"
                  sm="6"
                >
                  <p>Find Your Dream Job!</p>
                  <VBtn variant="outlined" @click="viewJobs('https://ca.jobomas.com')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job2" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>

          <!-- Eluta -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 2 ? 'primary' : ''"
            >
              ELUTA
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow
                class="ma-0 mt-4"
              >
                <VCol
                  cols="12"
                  sm="6"
                >
                  <p>Eluta.ca is a job search engine that specializes in finding new jobs directly from employer websites. Our site is managed by the Canada's Top 100 Employers project and indexes more direct-employer jobs than any other search engine in Canada. Our site launched on June 8, 2006 and is used by over 7 million unique visitors from Canada each year.</p>
                
                  <VBtn variant="outlined" @click="viewJobs('https://www.eluta.ca')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job3" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>

          <!-- JOB! LLICO -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 3 ? 'primary' : ''"
            >
              JOB! LLICO
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow
                style="background-color: #eee;"
                class="ma-0 mt-4 mb-2"
              >
                <VCol
                  cols="12"
                  sm="6"
                  style="background-color: #fff;"
                >
                  <p>Find Your Dream Job!</p>
                  <VBtn variant="outlined" @click="viewJobs('https://www.jobillico.com/en')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                  class="mb-2 text-center"
                >
                  <VImg :src="job4" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>
          
          <!-- BILINGUAL SOURCE -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 4 ? 'primary' : ''"
            >
              BILINGUAL SOURCE
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow
                class="ma-0 mt-4"
              >
                <VCol
                  cols="12"
                  sm="6"
                >
                  <p>We've been successfully matching the best French / English bilingual candidates with Canada's leading organizations since 1984.</p>

                  <VBtn variant="outlined" @click="viewJobs('https://bilingualsource.com/recruitment')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job5" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>
          
          <!-- BILINGUAL SOURCE -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 5 ? 'primary' : ''"
            >
              MONSTER
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow
                class="ma-0 mt-4"
              >
                <VCol
                  cols="12"
                  sm="6"
                >
                  <p>Find Your Dream Job!</p>
                  <VBtn variant="outlined" @click="viewJobs('https://www.monster.ca')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job6" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>
          
          
          <!-- BILINGUAL SOURCE -->
          <VExpansionPanel elevation="0">
            <VExpansionPanelTitle
              :color="openedPanel == 6 ? 'primary' : ''"
            >
              GLASSDOOR
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VRow
                class="ma-0 mt-4"
              >
                <VCol
                  cols="12"
                  sm="6"
                >
                  <p>So, what is Glassdoor? We're a thriving community for workplace conversations, driven by a simple mission: helping people everywhere find jobs and companies they love.</p>
                
                  <VBtn variant="outlined" @click="viewJobs('https://www.glassdoor.ca/Job/index.htm')">
                    View Jobs
                  </VBtn>
                </VCol>

                <VCol
                  cols="12"
                  sm="6"
                >
                  <VImg :src="job7" />
                </VCol>
              </VRow>
            </VExpansionPanelText>
          </VExpansionPanel>
          
        </VExpansionPanels>
      </VCardText>
    </VCard>

  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
