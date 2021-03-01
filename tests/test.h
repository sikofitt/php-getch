
typedef struct _iobuf
{
    char*   _ptr;
    int _cnt;
    char*   _base;
    int _flag;
    int _file;
    int _charbuf;
    int _bufsiz;
    char*   _tmpfname;
} FILE;

FILE *stdin;

FILE *fopen(const char *filename, const char *mode);

int ungetc(int ch, FILE *stream);

