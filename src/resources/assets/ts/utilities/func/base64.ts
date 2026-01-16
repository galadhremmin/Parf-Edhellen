/**
 * Converts a base64url string to an ArrayBuffer (as Uint8Array).
 * 
 * Base64url is URL-safe base64 encoding that uses '-' and '_' instead of '+' and '/',
 * and omits padding '=' characters.
 * 
 * @param base64url - The base64url string to decode
 * @returns A Uint8Array containing the decoded binary data
 */
export const base64urlToArrayBuffer = (base64url: string): Uint8Array => {
    // Convert base64url to standard base64
    let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    
    // Add padding if needed (base64 requires length to be multiple of 4)
    while (base64.length % 4) {
        base64 += '=';
    }
    
    // Decode using atob
    const binaryString = atob(base64);
    
    // Convert binary string to Uint8Array
    return Uint8Array.from(binaryString, c => c.charCodeAt(0));
};

/**
 * Converts an ArrayBuffer or Uint8Array to a base64 string.
 * 
 * @param arrayBuffer - The ArrayBuffer or Uint8Array to encode
 * @returns A base64-encoded string
 */
export const arrayBufferToBase64 = (arrayBuffer: ArrayBuffer | Uint8Array): string => {
    const uint8Array = arrayBuffer instanceof Uint8Array 
        ? arrayBuffer 
        : new Uint8Array(arrayBuffer);
    
    // Convert Uint8Array to binary string, then to base64
    const binaryString = String.fromCharCode(...uint8Array);
    return btoa(binaryString);
};

/**
 * Converts an ArrayBuffer or Uint8Array to a base64url string.
 * 
 * Base64url is URL-safe base64 encoding that uses '-' and '_' instead of '+' and '/',
 * and omits padding '=' characters.
 * 
 * @param arrayBuffer - The ArrayBuffer or Uint8Array to encode
 * @returns A base64url-encoded string (URL-safe, no padding)
 */
export const arrayBufferToBase64url = (arrayBuffer: ArrayBuffer | Uint8Array): string => {
    // First convert to standard base64
    const base64 = arrayBufferToBase64(arrayBuffer);
    
    // Convert to base64url: replace + with -, / with _, and remove padding
    return base64
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
};
