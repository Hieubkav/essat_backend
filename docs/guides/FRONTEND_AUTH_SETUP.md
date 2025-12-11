# Frontend Auth Setup Guide

## Yêu cầu

Backend đã setup auth endpoints. Frontend cần:
1. Gọi API login/register
2. Lưu token 
3. Tạo middleware để bảo vệ routes
4. Setup axios/fetch interceptor gửi token

## 1. Environment Variables

```bash
# .env.local
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

## 2. Setup Axios Instance

```typescript
// lib/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
});

// Add token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

## 3. Auth Service

```typescript
// lib/auth.ts
import api from './api';

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  is_admin: boolean;
}

export interface LoginResponse {
  user: User;
  access_token: string;
  token_type: string;
}

export const authService = {
  async register(data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    const response = await api.post<{ data: User }>('/auth/register', data);
    return response.data.data;
  },

  async login(email: string, password: string) {
    const response = await api.post<{ data: LoginResponse }>('/auth/login', {
      email,
      password,
    });
    const { access_token, user } = response.data.data;
    localStorage.setItem('auth_token', access_token);
    return user;
  },

  async getMe() {
    const response = await api.get<{ data: User }>('/auth/me');
    return response.data.data;
  },

  async logout() {
    await api.post('/auth/logout');
    localStorage.removeItem('auth_token');
  },

  getToken() {
    return localStorage.getItem('auth_token');
  },

  isAuthenticated() {
    return !!localStorage.getItem('auth_token');
  },
};
```

## 4. Middleware (Next.js 16)

```typescript
// app/middleware.ts
import { NextRequest, NextResponse } from 'next/server';

const publicRoutes = ['/login', '/register'];
const adminRoutes = ['/admin'];

export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;
  const pathname = request.nextUrl.pathname;

  // Check if route requires auth
  if (adminRoutes.some(route => pathname.startsWith(route)) && !token) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  // Redirect authenticated users away from public routes
  if (publicRoutes.includes(pathname) && token) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
```

## 5. Login Page

```typescript
// app/login/page.tsx
'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { authService } from '@/lib/auth';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const user = await authService.login(email, password);
      
      if (user.is_admin) {
        router.push('/admin');
      } else {
        router.push('/dashboard');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100">
      <div className="w-full max-w-md bg-white rounded-lg shadow p-8">
        <h1 className="text-2xl font-bold mb-6">Login</h1>

        {error && (
          <div className="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
              required
            />
          </div>

          <div className="mb-6">
            <label className="block text-sm font-medium mb-2">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
              required
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Loading...' : 'Login'}
          </button>
        </form>
      </div>
    </div>
  );
}
```

## 6. Protected Admin Layout

```typescript
// app/admin/layout.tsx
'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { authService, User } from '@/lib/auth';

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const token = authService.getToken();
        if (!token) {
          router.push('/login');
          return;
        }

        const userData = await authService.getMe();
        
        if (!userData.is_admin) {
          router.push('/unauthorized');
          return;
        }

        setUser(userData);
      } catch (error) {
        router.push('/login');
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, [router]);

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!user) {
    return null;
  }

  return (
    <div>
      <nav className="bg-gray-800 text-white p-4">
        <div className="container mx-auto flex justify-between items-center">
          <h1>Admin Panel</h1>
          <button
            onClick={() => {
              authService.logout();
              router.push('/login');
            }}
            className="bg-red-600 px-4 py-2 rounded"
          >
            Logout
          </button>
        </div>
      </nav>
      <main className="container mx-auto p-4">{children}</main>
    </div>
  );
}
```

## 7. User Context (Optional)

```typescript
// context/AuthContext.tsx
'use client';

import {
  createContext,
  useContext,
  useEffect,
  useState,
} from 'react';
import { authService, User } from '@/lib/auth';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<User>;
  logout: () => Promise<void>;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType>({
  user: null,
  loading: true,
  login: async () => {
    throw new Error('Not implemented');
  },
  logout: async () => {},
  isAuthenticated: false,
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const checkAuth = async () => {
      try {
        if (authService.isAuthenticated()) {
          const userData = await authService.getMe();
          setUser(userData);
        }
      } catch (error) {
        authService.logout();
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (email: string, password: string) => {
    const userData = await authService.login(email, password);
    setUser(userData);
    return userData;
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        login,
        logout,
        isAuthenticated: !!user,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

## 8. Usage in Components

```typescript
// app/admin/page.tsx
'use client';

import { useAuth } from '@/context/AuthContext';

export default function AdminPage() {
  const { user, logout } = useAuth();

  return (
    <div>
      <h1>Welcome, {user?.name}</h1>
      <p>Email: {user?.email}</p>
      <button onClick={logout}>Logout</button>
    </div>
  );
}
```

## Testing

### Test Register
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'
```

### Test Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123"
  }'
```

### Test Protected Route
```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {access_token}"
```
