const app = {
  apiUrl: import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api',
  appUrl: import.meta.env.VITE_APP_URL || 'http://localhost:5173',
  environment: import.meta.env.VITE_APP_ENV || 'local',
  userType: import.meta.env.VITE_APP_USER_TYPE_KEY || 'app_user_type',
  tokenKey: import.meta.env.VITE_APP_TOKEN_KEY || 'app_token',
  userRolesKey: import.meta.env.VITE_APP_USER_ROLES_KEY || 'app_user_roles',
  userPermissionsKey: import.meta.env.VITE_APP_USER_PERMISSIONS_KEY || 'app_user_permissions',
  moduleLocal: import.meta.env.VITE_APP_MODULE_LOCAL === 'true',
  name: import.meta.env.VITE_APP_NAME || 'My App',
  logo: import.meta.env.VITE_APP_LOGO || '',
}

export default app
