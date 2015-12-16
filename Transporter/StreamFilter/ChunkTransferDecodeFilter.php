<?php
namespace Poirot\HttpAgent\Transporter\StreamFilter;

use Poirot\Stream\Filter\AbstractFilter;

/**
 * A stream filter for removing the 'chunking' of a 'Transfer-Encoding: chunked'
 * http response
 *
 * The http stream wrapper on php does not support chunked transfer
 * encoding, making this filter necessary.
 *
 * @license BSD
 * @author Francis Avila
 */
class ChunkTransferDecodeFilter extends AbstractFilter
{
    /** @var int bytes remaining in the current chunk */
    protected $chunkremaining = 0;

    /**
     * Filter Stream Through Buckets
     *
     * @param resource $in userfilter.bucket brigade
     *                         pointer to a group of buckets objects containing the data to be filtered
     * @param resource $out userfilter.bucket brigade
     *                         pointer to another group of buckets for storing the converted data
     * @param int $consumed counter passed by reference that must be incremented by the length
     *                         of converted data
     * @param boolean $closing flag that is set to TRUE if we are in the last cycle and the stream is
     *                           about to close
     * @return int
     */
    function filter($in, $out, &$consumed, $closing)
    {
        // $in and $out are opaque "bucket brigade" objects which consist of a
        // sequence of opaque "buckets", which contain the actual stream data.
        // The only way to use these objects is the stream_bucket_* functions.
        // Unfortunately, there doesn't seem to be any way to access a bucket
        // without turning it into a string using stream_bucket_make_writeable(),
        // even if you want to pass the bucket along unmodified.

        // Each call to this pops a bucket from the bucket brigade and
        // converts it into an object with two properties: datalen and data.
        // This same object interface is accepted by stream_bucket_append().
        while ($bucket = stream_bucket_make_writeable($in)) {
            $outbuffer = '';
            $offset = 0;
            // Loop through the string.  For efficiency, we don't advance a character
            // at a time but try to zoom ahead to where we think the next chunk
            // boundary should be.

            // Since the stream filter divides the data into buckets arbitrarily,
            // we have to maintain state ($this->chunkremaining) across filter() calls.
            while ($offset < $bucket->datalen) {
                if ($this->chunkremaining===0) {
                    // start of new chunk, or the start of the transfer
                    $firstline = strpos($bucket->data, "\r\n", $offset);
                    $chunkline = substr($bucket->data, $offset, $firstline-$offset);
                    $chunklen = current(explode(';', $chunkline, 2)); // ignore MIME-like extensions
                    $chunklen = trim($chunklen);
                    if (!ctype_xdigit($chunklen))
                    // There should have been a chunk length specifier here, but since
                    // there are non-hex digits something must have gone wrong.
                        return PSFS_ERR_FATAL;

                    $this->chunkremaining = hexdec($chunklen);
                    // $firstline already includes $offset in it
                    $offset = $firstline+2; // +2 is CRLF
                    if ($this->chunkremaining===0) //end of the transfer
                        break;  // ignore possible trailing headers

                }
                // get as much data as available in a single go...
                $nibble = substr($bucket->data, $offset, $this->chunkremaining);
                $nibblesize = strlen($nibble);
                $offset += $nibblesize; // ...but recognize we may not have got all of it
                if ($nibblesize === $this->chunkremaining)
                    $offset += 2; // skip over trailing CRLF

                $this->chunkremaining -= $nibblesize;
                $outbuffer .= $nibble;
            }

            $consumed += $bucket->datalen;
            $bucket->data = $outbuffer;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
