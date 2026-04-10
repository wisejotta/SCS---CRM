/*
File: /project-structure.txt
Description: Full scaffold of TaskForge web app
*/

/project-root
├── app                      # Next.js App Router Pages
│   ├── layout.tsx
│   ├── page.tsx
│   └── (auth)               # Auth pages
│       ├── login/page.tsx
│       └── signup/page.tsx
├── components              # Shared UI Components
│   ├── Button.tsx
│   ├── Modal.tsx
│   ├── Avatar.tsx
│   └── ThemeToggle.tsx
├── features                # Domain Modules (self-contained)
│   ├── auth
│   │   ├── hooks/useAuth.ts
│   │   ├── services/authService.ts
│   │   └── components/AuthForm.tsx
│   ├── tasks
│   │   ├── hooks/useTasks.ts
│   │   ├── services/taskService.ts
│   │   └── components/TaskCard.tsx
│   ├── team
│   │   └── ...
│   └── calendar
│       └── ...
├── lib                     # Global helpers/utils
│   ├── supabaseClient.ts
│   └── api.ts
├── hooks                   # Custom reusable hooks
│   ├── useToast.ts
│   └── useTheme.ts
├── types                   # Global TS types
│   └── index.ts
├── styles                  # Tailwind, global styles
│   ├── globals.css
│   └── tailwind.config.ts
├── public                  # Static assets
│   └── logo.svg
├── tests                   # Unit and E2E tests
│   ├── auth.test.ts
│   └── task.test.ts
├── .env.local              # Environment secrets
├── .eslintrc.json          # ESLint config
├── .prettierrc             # Prettier config
├── package.json            # Project dependencies
├── tsconfig.json           # TypeScript config
└── README.md               # Project docs
