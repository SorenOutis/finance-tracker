/**
 * Generates an absolute URL from a relative path.
 *
 * When a baseUrl is provided (from Inertia's shared appUrl prop), it's used directly
 * so the URL is available during SSR. Falls back to window.location.origin when no
 * baseUrl is given (client-side fallback).
 */
export function absoluteUrl(path: string, baseUrl?: string): string {
    const origin = baseUrl || (typeof window !== 'undefined' ? window.location.origin : '')
    if (!origin) return path
    return new URL(path, origin).href
}
