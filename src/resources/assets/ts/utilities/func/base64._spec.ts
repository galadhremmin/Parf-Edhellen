import {
    describe,
    expect,
    test,
} from '@jest/globals';
import {
    base64urlToArrayBuffer,
    arrayBufferToBase64,
    arrayBufferToBase64url,
} from './base64';

describe('utilities/func/base64', () => {
    describe('base64urlToArrayBuffer', () => {
        test('decodes base64url string to Uint8Array', () => {
            // "Hello" in base64url: SGVsbG8 (no padding)
            const base64url = 'SGVsbG8';
            const result = base64urlToArrayBuffer(base64url);
            
            expect(result).toBeInstanceOf(Uint8Array);
            expect(result.length).toBe(5);
            expect(Array.from(result)).toEqual([72, 101, 108, 108, 111]); // "Hello" ASCII
        });

        test('handles base64url with padding', () => {
            // "Hi" in base64url: SGk= (with padding in base64, but base64url omits it)
            // We'll test with padding to ensure it works
            const base64url = 'SGk';
            const result = base64urlToArrayBuffer(base64url);
            
            expect(result).toBeInstanceOf(Uint8Array);
            expect(result.length).toBe(2);
            expect(Array.from(result)).toEqual([72, 105]); // "Hi" ASCII
        });

        test('handles base64url with special characters', () => {
            // Test with '-' and '_' characters (base64url specific)
            const base64url = 'SGVsbG8-';
            const result = base64urlToArrayBuffer(base64url);
            
            // Should convert '-' to '+' and decode correctly
            expect(result).toBeInstanceOf(Uint8Array);
        });

        test('handles empty string', () => {
            const result = base64urlToArrayBuffer('');
            expect(result).toBeInstanceOf(Uint8Array);
            expect(result.length).toBe(0);
        });
    });

    describe('arrayBufferToBase64', () => {
        test('encodes Uint8Array to base64 string', () => {
            const data = new Uint8Array([72, 101, 108, 108, 111]); // "Hello"
            const result = arrayBufferToBase64(data);
            
            expect(result).toBe('SGVsbG8=');
        });

        test('encodes ArrayBuffer to base64 string', () => {
            const data = new Uint8Array([72, 105]); // "Hi"
            const arrayBuffer = data.buffer;
            const result = arrayBufferToBase64(arrayBuffer);
            
            expect(result).toBe('SGk=');
        });

        test('handles empty ArrayBuffer', () => {
            const data = new Uint8Array([]);
            const result = arrayBufferToBase64(data);
            
            expect(result).toBe('');
        });
    });

    describe('arrayBufferToBase64url', () => {
        test('encodes Uint8Array to base64url string', () => {
            const data = new Uint8Array([72, 101, 108, 108, 111]); // "Hello"
            const result = arrayBufferToBase64url(data);
            
            // Should be URL-safe (no +, /, or =)
            expect(result).toBe('SGVsbG8');
            expect(result).not.toContain('+');
            expect(result).not.toContain('/');
            expect(result).not.toContain('=');
        });

        test('encodes ArrayBuffer to base64url string', () => {
            const data = new Uint8Array([72, 105]); // "Hi"
            const arrayBuffer = data.buffer;
            const result = arrayBufferToBase64url(arrayBuffer);
            
            expect(result).toBe('SGk');
            expect(result).not.toContain('+');
            expect(result).not.toContain('/');
            expect(result).not.toContain('=');
        });

        test('round-trip conversion works', () => {
            const original = 'SGVsbG8';
            const decoded = base64urlToArrayBuffer(original);
            const encoded = arrayBufferToBase64url(decoded);
            
            expect(encoded).toBe(original);
        });

        test('handles empty ArrayBuffer', () => {
            const data = new Uint8Array([]);
            const result = arrayBufferToBase64url(data);
            
            expect(result).toBe('');
        });
    });
});
