// Chunking Worker
// Handles large file chunking for upload without blocking the UI

self.onmessage = function(e) {
  const { file, chunkSize } = e.data;
  
  if (!file) {
    self.postMessage({ error: 'No file provided' });
    return;
  }

  chunkFile(file, chunkSize || 1024 * 1024).then(chunks => {
    self.postMessage({ 
      type: 'chunks_ready',
      chunks: chunks,
      filename: file.name,
      totalSize: file.size 
    });
  }).catch(error => {
    self.postMessage({ 
      type: 'error',
      error: error.message 
    });
  });
};

// Chunk file into manageable pieces
async function chunkFile(file, chunkSize) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    const fileSize = file.size;
    let offset = 0;
    let chunkIndex = 0;

    function readNextChunk() {
      if (offset >= fileSize) {
        resolve(chunks);
        return;
      }

      const slice = file.slice(offset, offset + chunkSize);
      const reader = new FileReader();

      reader.onload = function(e) {
        const chunk = {
          index: chunkIndex++,
          data: e.target.result,
          offset: offset,
          size: e.target.result.byteLength,
          isLast: offset + e.target.result.byteLength >= fileSize
        };

        chunks.push(chunk);
        offset += e.target.result.byteLength;

        // Report progress
        const progress = Math.round((offset / fileSize) * 100);
        self.postMessage({ 
          type: 'progress',
          progress: progress,
          filename: file.name,
          chunkIndex: chunk.index 
        });

        // Continue reading next chunk
        setTimeout(readNextChunk, 0); // Yield to main thread
      };

      reader.onerror = function() {
        reject(new Error('Failed to read file chunk'));
      };

      reader.readAsArrayBuffer(slice);
    }

    readNextChunk();
  });
}

// Reconstruct file from chunks
function reconstructFile(chunks, filename, mimeType) {
  const blob = new Blob(chunks.map(chunk => chunk.data), { type: mimeType });
  return new File([blob], filename, { type: mimeType });
}

// Calculate checksum for chunk
async function calculateChecksum(chunk) {
  if ('crypto' in self && 'subtle' in self.crypto) {
    const hashBuffer = await self.crypto.subtle.digest('SHA-256', chunk.data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
  }
  
  // Fallback: simple length-based checksum
  return chunk.data.byteLength.toString(16);
}

// Export functions
self.reconstructFile = reconstructFile;
self.calculateChecksum = calculateChecksum;