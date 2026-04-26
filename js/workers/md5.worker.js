// MD5 Checksum Worker
// Handles MD5 calculation for files to prevent UI blocking

self.onmessage = function(e) {
  const { file } = e.data;
  
  if (!file) {
    self.postMessage({ error: 'No file provided' });
    return;
  }

  calculateMD5(file).then(checksum => {
    self.postMessage({ 
      checksum: checksum,
      filename: file.name,
      size: file.size 
    });
  }).catch(error => {
    self.postMessage({ 
      error: error.message,
      filename: file.name 
    });
  });
};

// Calculate MD5 checksum
async function calculateMD5(file) {
  return new Promise((resolve, reject) => {
    const chunkSize = 1024 * 1024; // 1MB chunks
    const fileSize = file.size;
    let offset = 0;
    const spark = new SparkMD5.ArrayBuffer();
    const fileReader = new FileReader();

    fileReader.onload = function(e) {
      try {
        spark.append(e.target.result);
        offset += e.target.result.byteLength;
        
        // Report progress
        const progress = Math.round((offset / fileSize) * 100);
        self.postMessage({ 
          type: 'progress',
          progress: progress,
          filename: file.name 
        });

        if (offset >= fileSize) {
          const checksum = spark.end();
          resolve(checksum);
        } else {
          loadNext();
        }
      } catch (error) {
        reject(error);
      }
    };

    fileReader.onerror = function() {
      reject(new Error('File reading failed'));
    };

    function loadNext() {
      const slice = file.slice(offset, offset + chunkSize);
      fileReader.readAsArrayBuffer(slice);
    }

    loadNext();
  });
}

// SparkMD5 implementation (simplified)
// In production, use the actual SparkMD5 library
class SparkMD5 {
  constructor() {
    this.blocks = [];
    this.blockCount = 0;
    this.length = 0;
    this.state = {
      a: 1732584193,
      b: -271733879,
      c: -1732584194,
      d: 271733878
    };
  }

  append(buffer) {
    const data = new Uint8Array(buffer);
    const blocks = this.blocks;
    const length = this.length;
    
    for (let i = 0; i < data.length; i++) {
      blocks[length + i] = data[i];
    }
    
    this.length += data.length;
    return this;
  }

  end(raw) {
    const blocks = this.blocks;
    const length = this.length;
    const state = this.state;
    
    // Pad message
    const padLen = length % 64 < 56 ? 56 - (length % 64) : 120 - (length % 64);
    const pad = new Uint8Array(padLen + 8);
    pad[0] = 0x80;
    
    // Add length in bits (little endian)
    const bitLen = length * 8;
    for (let i = 0; i < 8; i++) {
      pad[padLen + i] = (bitLen >>> (i * 8)) & 0xff;
    }
    
    // Process blocks
    const data = new Uint8Array(length + padLen + 8);
    data.set(blocks);
    data.set(pad, length);
    
    const hash = this.md5(data, state);
    
    // Convert to hex string
    let hex = '';
    for (let i = 0; i < 4; i++) {
      hex += this.lpad((hash[i] >>> 0).toString(16), '0', 8);
    }
    
    return hex;
  }

  // MD5 algorithm implementation
  md5(data, state) {
    const x = this.bytesToWords(data);
    const len = data.length * 8;
    
    x[len >> 5] |= 0x80 << ((len) % 32);
    x[(((len + 64) >>> 9) << 4) + 14] = len;
    
    let a = state.a;
    let b = state.b;
    let c = state.c;
    let d = state.d;
    
    for (let i = 0; i < x.length; i += 16) {
      const olda = a;
      const oldb = b;
      const oldc = c;
      const oldd = d;
      
      a = this.md5ff(a, b, c, d, x[i + 0], 7, -680876936);
      d = this.md5ff(d, a, b, c, x[i + 1], 12, -389564586);
      c = this.md5ff(c, d, a, b, x[i + 2], 17, 606105819);
      b = this.md5ff(b, c, d, a, x[i + 3], 22, -1044525330);
      a = this.md5ff(a, b, c, d, x[i + 4], 7, -176418897);
      d = this.md5ff(d, a, b, c, x[i + 5], 12, 1200080426);
      c = this.md5ff(c, d, a, b, x[i + 6], 17, -1473231341);
      b = this.md5ff(b, c, d, a, x[i + 7], 22, -45705983);
      a = this.md5ff(a, b, c, d, x[i + 8], 7, 1770035416);
      d = this.md5ff(d, a, b, c, x[i + 9], 12, -1958414417);
      c = this.md5ff(c, d, a, b, x[i + 10], 17, -42063);
      b = this.md5ff(b, c, d, a, x[i + 11], 22, -1990404162);
      a = this.md5ff(a, b, c, d, x[i + 12], 7, 1804603682);
      d = this.md5ff(d, a, b, c, x[i + 13], 12, -40341101);
      c = this.md5ff(c, d, a, b, x[i + 14], 17, -1502002290);
      b = this.md5ff(b, c, d, a, x[i + 15], 22, 1236535329);
      
      // More MD5 rounds...
      a = this.md5gg(a, b, c, d, x[i + 1], 5, -165796510);
      d = this.md5gg(d, a, b, c, x[i + 6], 9, -1069501632);
      c = this.md5gg(c, d, a, b, x[i + 11], 14, 643717713);
      b = this.md5gg(b, c, d, a, x[i + 0], 20, -373897302);
      a = this.md5gg(a, b, c, d, x[i + 5], 5, -701558691);
      d = this.md5gg(d, a, b, c, x[i + 10], 9, 38016083);
      c = this.md5gg(c, d, a, b, x[i + 15], 14, -660478335);
      b = this.md5gg(b, c, d, a, x[i + 4], 20, -405537848);
      
      a = this.md5hh(a, b, c, d, x[i + 5], 4, -378558);
      d = this.md5hh(d, a, b, c, x[i + 8], 11, -2022574463);
      c = this.md5hh(c, d, a, b, x[i + 11], 16, 1839030562);
      b = this.md5hh(b, c, d, a, x[i + 14], 23, -35309556);
      a = this.md5hh(a, b, c, d, x[i + 1], 4, -1530992060);
      d = this.md5hh(d, a, b, c, x[i + 4], 11, 1272893353);
      c = this.md5hh(c, d, a, b, x[i + 7], 16, -155497632);
      b = this.md5hh(b, c, d, a, x[i + 10], 23, -1094730640);
      
      a = this.md5ii(a, b, c, d, x[i + 0], 6, -680876936);
      d = this.md5ii(d, a, b, c, x[i + 7], 10, 3905402710);
      c = this.md5ii(c, d, a, b, x[i + 14], 15, -568446438);
      b = this.md5ii(b, c, d, a, x[i + 5], 21, -1019803690);
      
      a = this.safeAdd(a, olda);
      b = this.safeAdd(b, oldb);
      c = this.safeAdd(c, oldc);
      d = this.safeAdd(d, oldd);
    }
    
    return [a, b, c, d];
  }

  // Helper functions
  bytesToWords(bytes) {
    const words = [];
    for (let i = 0, b = 0; i < bytes.length; i++, b += 8) {
      words[b >>> 5] |= bytes[i] << (24 - b % 32);
    }
    return words;
  }

  lpad(str, pad, len) {
    while (str.length < len) str = pad + str;
    return str;
  }

  safeAdd(x, y) {
    const lsw = (x & 0xffff) + (y & 0xffff);
    const msw = (x >> 16) + (y >> 16) + (lsw >> 16);
    return (msw << 16) | (lsw & 0xffff);
  }

  md5ff(a, b, c, d, x, s, t) {
    return this.calcM(a, b, c, d, x, s, t, (x, y) => (x & y) | ((~x) & z));
  }

  md5gg(a, b, c, d, x, s, t) {
    return this.calcM(a, b, c, d, x, s, t, (x, y, z) => (x & z) | (y & (~z)));
  }

  md5hh(a, b, c, d, x, s, t) {
    return this.calcM(a, b, c, d, x, s, t, (x, y, z) => x ^ y ^ z);
  }

  md5ii(a, b, c, d, x, s, t) {
    return this.calcM(a, b, c,d, x, s, t, (x, y, z) => y ^ (x | (~z)));
  }

  calcM(a, b, c, d, x, s, t, fn) {
    const z = c;
    const temp = fn(a, b, z);
    let result = this.safeAdd(a, this.safeAdd(this.safeAdd(temp, x), t));
    result = this.safeAdd(this.rotateLeft(result, s), b);
    return result;
  }

  rotateLeft(num, cnt) {
    return (num << cnt) | (num >>> (32 - cnt));
  }
}

// Use native crypto if available
if ('crypto' in self && 'subtle' in self.crypto) {
  async function calculateMD5Native(file) {
    const buffer = await file.arrayBuffer();
    const hashBuffer = await self.crypto.subtle.digest('MD5', buffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
  }
}
