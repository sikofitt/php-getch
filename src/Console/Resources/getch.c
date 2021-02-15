#include <stdio.h>
#include <termios.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <fcntl.h>

#define CTRL_KEY(k) ((k) & 0x1f)

static struct termios oldattr;
static int stdin_flags;

/*
static char *strrev(char *str)
{
      char *p1, *p2;

      if (! str || ! *str)
            return str;
      for (p1 = str, p2 = str + strlen(str) - 1; p2 > p1; ++p1, --p2)
      {
            *p1 ^= *p2;
            *p2 ^= *p1;
            *p1 ^= *p2;
      }
      return str;
}
*/
static void setNormalMode(void)
{
    tcsetattr(STDIN_FILENO, TCSANOW, &oldattr);
    fcntl(STDIN_FILENO, F_SETFL, stdin_flags);
}

static void setRawMode(void)
{
    tcgetattr(STDIN_FILENO, &oldattr);
    atexit(setNormalMode);

    struct termios raw = oldattr;

    raw.c_iflag &= ~(BRKINT | ICRNL | INPCK | ISTRIP | IXON);
    raw.c_oflag &= ~(OPOST);
    raw.c_cflag |= (CS8);
    raw.c_lflag &= ~(ECHO | ICANON | IEXTEN | ISIG);
    raw.c_cc[VMIN] = 0;
    raw.c_cc[VTIME] = 1;

    tcsetattr(STDIN_FILENO, TCSANOW, &raw);

}

// F1 - 59
// F2 - 60,
// F3 - 61,
// F2 - 62
// F1-12 = 59 - 68
enum special_keycodes {
    GETCH_F1 = 59,
    GETCH_F2,
    GETCH_F3,
    GETCH_F4,
    GETCH_F5,
    GETCH_F6,
    GETCH_F7,
    GETCH_F8,
    GETCH_F9,
    GETCH_F10,
    GETCH_UP_ARROW = 72,
    GETCH_LEFT_ARROW = 75,
    GETCH_RIGHT_ARROW = 77,
    GETCH_DOWN_ARROW = 80,
    GETCH_DELETE = 83,
    GETCH_F11 = 87,
    GETCH_F12,
    GETCH_HOME = 102,
    GETCH_PGUP = 104,
    GETCH_END = 107,
    GETCH_PGDOWN = 109,
    GETCH_INSERT
};

void _ungetc(int c) {
    ungetc(c, stdin);
}

int readKey(void) {

int bytesRead;

    char key;

    while ((key = fgetc(stdin)) == EOF) {
    }

    if (key == '\x1b') {
        char seq[4];
        if ((seq[0] = getchar()) == EOF) return '\x1b'; // [
        if ((seq[1] = getchar()) == EOF) return '\x1b';

        if (seq[0] == '[') {
            if(seq[1] == 'H') { _ungetc(GETCH_HOME); return 0; }
            if(seq[1] == 'F') { _ungetc(GETCH_END); return 0; }

            if (seq[1] >= '0' && seq[1] <= '9') {
                if ((seq[2] = getchar()) == EOF) return '\x1b';
                if (seq[2] == '~') {
                    switch (seq[1]) {
                        case '1': _ungetc(GETCH_HOME); return 0;
                        case '2': _ungetc(GETCH_INSERT); return 0;
                        case '3': _ungetc(GETCH_DELETE); return 0;
                        case '4': _ungetc(GETCH_END); return 0;
                        case '5': _ungetc(GETCH_PGUP); return 0;
                        case '6': _ungetc(GETCH_PGDOWN); return 0;
                        case '7': _ungetc(GETCH_HOME); return 0;
                        case '8': _ungetc(GETCH_END); return 0;
                    }
                } else if(seq[2] >= '0' && seq[2] <= '9') {
                    if ((seq[3] = getchar()) == EOF) return '\x1b';
                    if(seq[3] != '~') return seq[3];

                    switch(seq[2]) {
                        case '5': _ungetc(GETCH_F5); return 0;
                        case '7': _ungetc(GETCH_F6); return 0;
                        case '8': _ungetc(GETCH_F7); return 0;
                        case '9': _ungetc(GETCH_F8); return 0;
                        case '0': _ungetc(GETCH_F9); return 0;
                        case '1': _ungetc(GETCH_F10); return 0;
                        case '3': _ungetc(GETCH_F11); return 0;
                        case '4': _ungetc(GETCH_F12); return 0;
                    }

                } else { return seq[2]; }
            } else {
                    switch (seq[1]) {
                    case 'A': _ungetc(GETCH_UP_ARROW); return 0;
                    case 'B': _ungetc(GETCH_DOWN_ARROW); return 0;
                    case 'C': _ungetc(GETCH_RIGHT_ARROW); return 0;
                    case 'D': _ungetc(GETCH_LEFT_ARROW); return 0;
                    case 'H': _ungetc(GETCH_HOME); return 0;
                    case 'F': _ungetc(GETCH_END); return 0;
                }
            }
        }
        else if (seq[0] == 'O') {
            switch (seq[1]) {
                case 'H': _ungetc(GETCH_HOME); return 0;
                case 'F': _ungetc(GETCH_END); return 0;
                case 'P': _ungetc(GETCH_F1); return 0;
                case 'Q': _ungetc(GETCH_F2); return 0;
                case 'R': _ungetc(GETCH_F3); return 0;
                case 'S': _ungetc(GETCH_F4); return 0;
            }
        }
        return '\x1b';
    } else {
        return key;
    }
}

int _getch(void) {

    setRawMode();

    int key = readKey();

    setNormalMode();

    return key;
}

/* reads from keypress, doesn't echo */
int old_getch(void)
{
    int ch;

    setRawMode();
    atexit(setNormalMode);

    ch = getchar();

    setNormalMode();

if(ch == 27) {

char sequence[4];

if (read(STDIN_FILENO, &sequence[0], 1) != 1) return 27; // [
if (read(STDIN_FILENO, &sequence[1], 1) != 1) return 27; // 0-9

    switch(ch)
    {
      case 79: // F1-F4
        ch = getchar();
        switch(ch) {
          case 80: // F1, [ESC]OP
            _ungetc(GETCH_F1);
            return 0;
            break;
          case 81: // F2, [ESC]OQ
            _ungetc(GETCH_F2);
            return 0;
            break;
          case 82: // F3,, [ESC]OR
            _ungetc(61);
            return 0;
          case 83: // F4, , [ESC]OS
            _ungetc(62);
            return 0;
        }
      case 91: // [, Everything else
        ch = getchar();
        switch(ch) {
          case 49: // 1, F5-F8, HOME
            ch = getchar();
            switch(ch) {
              case 53: // 5, F5
                getchar(); // get the ~
                _ungetc(63);
                return 0;
              case 55: // 7, F6
                getchar();
                _ungetc(64);
                return 0;
              case 56: // 8, F7
                getchar();
                _ungetc(65);
                return 0;
              case 57: // 9, F8
                getchar();
                _ungetc(66);
                return 0;
              case 126: // ~, HOME
                _ungetc(102);
                return 0;
            }
          case 50: // 2, F9-F12, INSERT
            ch = getchar();
            switch(ch) {
              case 48: // 0, F9, [ESC][20~
                getchar();
                _ungetc(67);
                return 0;
              case 49: // 1, F10, [ESC][21~
                getchar();
                _ungetc(68);
                return 0;
              case 51: // 3, F11, [ESC][23~
                getchar();
                _ungetc(87);
                return 0;
              case 52: // 4, F12, [ESC][24~
                getchar();
                _ungetc(88);
                return 0;
              case 126: // ~, INSERT, [ESC][2~
                _ungetc(110);
                return 0;
            }
            case 51: // 3, DELETE, [ESC][3~
              getchar(); // ~
              _ungetc(83);
              return 0;
            case 52: // 4, END, [ESC][4~
              getchar(); // ~
              _ungetc(107);
              return 0;
            case 53: // 5, PGUP, [ESC][5~
              getchar(); // ~
              _ungetc(104);
              return 0;
            case 54: // 6, PGDN, [ESC][6~
              getchar(); // ~
              _ungetc(109);
              return 0;
            case 65: // A, UP_ARROW, [ESC][A //72
              _ungetc(72);
              return 0;
            case 66: // B, DOWN_ARROW, [ESC][B // 80
              _ungetc(80);
              return 0;
            case 67: // C, RIGHT_ARROW, [ESC][C /77
              _ungetc(77);
              return 0;
            case 68: // D, LEFT_ARROW, [ESC][D //75
              _ungetc(75);
              return 0;
        }
    }
  }
    return ch;
}

int _ungetch(char ch)
{
    return ungetc(ch, stdin);
}