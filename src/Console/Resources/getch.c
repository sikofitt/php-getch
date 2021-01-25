#include <stdio.h>
#include <termios.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
static struct termios oldattr;

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


static void setRawMode(void)
{
    struct termios newattr;

    tcgetattr(STDIN_FILENO, &oldattr);
    newattr = oldattr;
    cfmakeraw(&newattr);
    tcsetattr(STDIN_FILENO, TCSANOW, &newattr);

}
static void setNormalMode(void)
{
    tcsetattr(STDIN_FILENO, TCSANOW, &oldattr);
}


/* reads from keypress, doesn't echo */
int _getch(void)
{
    int ch;

    setRawMode();
    atexit(setNormalMode);

    ch = getchar();

    setNormalMode();

    return ch;
}

int _ungetch(char ch)
{
    return ungetc(ch, stdin);
}