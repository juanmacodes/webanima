'use client';

import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';

type Badge = {
  id: string;
  title: string;
  description: string;
};

type GamificationState = {
  xp: number;
  badges: Badge[];
  addXp: (amount: number) => void;
  grantBadge: (badge: Badge) => void;
  reset: () => void;
};

export const useGamificationStore = create<GamificationState>()(
  persist(
    (set) => ({
      xp: 0,
      badges: [],
      addXp: (amount) =>
        set((state) => ({
          xp: Math.min(99999, state.xp + amount)
        })),
      grantBadge: (badge) =>
        set((state) => {
          if (state.badges.some((existing) => existing.id === badge.id)) {
            return state;
          }
          return {
            badges: [...state.badges, badge],
            xp: Math.min(99999, state.xp + 25)
          };
        }),
      reset: () => set({ xp: 0, badges: [] })
    }),
    {
      name: 'anima-gamification',
      storage: createJSONStorage(() => {
        if (typeof window === 'undefined') {
          return {
            getItem: () => null,
            setItem: () => undefined,
            removeItem: () => undefined
          } as unknown as Storage;
        }
        return window.localStorage;
      })
    }
  )
);
