const user = JSON.parse(localStorage.getItem('user'))

let list = []

if(user.role == 'ADMIN') {
  list = list.concat([
    {
      title: 'Leads',
      icon: { icon: 'tabler-list-details' },
      children: [
        {
          title: 'All Leads',
          children: [
            {
              title: 'FILE OPENING',
              to: { name: 'leads-opening' },
            },
            {
              title: 'UPGRADE',
              to: { name: 'leads-upgrade' },
            },
          ],
        },
        {
          title: 'Assinged',
          to: { name: 'leads-assinged' },
        },
        {
          title: 'Reassign Pool',
          to: { name: 'reassign' },
        },
        {
          title: 'Closed',
          to: { name: 'closed' },
        }
      ],
    },
    {
      title: 'Agents',
      to: { name: 'agents' },
      icon: { icon: 'tabler-users' },
    },
    {
      title: 'Breaks',
      to: { name: 'breaks' },
      icon: { icon: 'tabler-milkshake' },
    },
    {
      title: 'Chargebacks',
      to: { name: 'chargebacks' },
      icon: { icon: 'tabler-arrow-back-up' },
    },
    {
      title: 'Unlisted Leads',
      icon: { icon: 'tabler-alert-octagon' },
      children: [
        {
          title: 'Disqualified',
          to: { name: 'unlisted-disqualified' },
        },
        {
          title: 'On hold',
          to: { name: 'unlisted-hold' },
        },
        {
          title: 'Risk',
          to: { name: 'unlisted-risk' },
        },
      ],
    },
    {
      title: 'Sales',
      icon: { icon: 'tabler-report-money' },
      children: [
        {
          title: 'FILE OPENING',
          to: { name: 'sales-fileopening' },
        },
        {
          title: 'UPGRADE',
          to: { name: 'sales-upgrade' },
        },
      ],
    },
  ])
}

export default list.concat([
  {
    title: 'Applications',
    to: { name: 'home' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'Calendar',
    to: { name: 'callbacks' },
    icon: { icon: 'tabler-calendar' },
  },
  {
    title: 'Opportunities',
    to: { name: 'opportunities' },
    icon: { icon: 'tabler-arrows-exchange' },
  },
])
