/*|~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~|*/
/*|      Program name:          Find_Solution_to_Puzzle     |*/
/*|      Creation date:         June 28, 1993               |*/
/*|      Last updated:          June 28, 1993               |*/
/*|      Author:                Cortland D. Starrett        |*/
/*|                                                         |*/
/*| Change Log:                                             |*/
/*|                                                         |*/
/*| Initial  date      Reason                               |*/
/*| ------------------------------------------------------- |*/
/*|   CDS    06/28/93  initial creation                     |*/
/*|                                                         |*/
/*|      Protocol -                                         |*/
/*|         % puz                                           |*/
/*|                                                         |*/
/*|      Function -                                         |*/
/*|         Program to brute force find answer to puzzle.   |*/
/*|_________________________________________________________|*/

#include <stdio.h>

unsigned short i[9], c[9];

main()
{
 
  unsigned short j, k, n;
  for ( j=0; j<=9; j++ ) {
   i[j]=1; c[j]=1;
  }
  c[9]=11;

  for ( i[9]=1; i[9]<=9; i[9]++ ) {
   c[i[9]] += 1;
   if (i[9] == 1) c[9] -= 1; else c[(i[9]+8) % 9] -= 1;
   for ( i[8]=1; i[8]<=9; i[8]++ ) {
    c[i[8]] += 1;
    if (i[8] == 1) c[9] -= 1; else c[(i[8]+8) % 9] -= 1;
    printf( "counting to 999...\n" );
    for ( i[7]=1; i[7]<=9; i[7]++ ) {
     c[i[7]] += 1;
     if (i[7] == 1) c[9] -= 1; else c[(i[7]+8) % 9] -= 1;
     printf( "                    ...  %u%u%u\n", i[9],i[8],i[7] );
     for ( i[6]=1; i[6]<=9; i[6]++ ) {
      c[i[6]] += 1;
      if (i[6] == 1) c[9] -= 1; else c[(i[6]+8) % 9] -= 1;
      for ( i[5]=1; i[5]<=9; i[5]++ ) {
       c[i[5]] += 1;
       if (i[5] == 1) c[9] -= 1; else c[(i[5]+8) % 9] -= 1;
       for ( i[4]=1; i[4]<=9; i[4]++ ) {
        c[i[4]] += 1;
        if (i[4] == 1) c[9] -= 1; else c[(i[4]+8) % 9] -= 1;
        for ( i[3]=1; i[3]<=9; i[3]++ ) {
         c[i[3]] += 1;
         if (i[3] == 1) c[9] -= 1; else c[(i[3]+8) % 9] -= 1;
         for ( i[2]=1; i[2]<=9; i[2]++ ) {
          c[i[2]] += 1;
          if (i[2] == 1) c[9] -= 1; else c[(i[2]+8) % 9] -= 1;
          for ( i[1]=1; i[1]<=9; i[1]++ ) {
           c[i[1]] += 1;
           if (i[1] == 1) c[9] -= 1; else c[(i[1]+8) % 9] -= 1;
           for ( i[0]=1; i[0]<=9; i[0]++ ) {
            c[i[0]] += 1;
            if (i[0] == 1) c[9] -= 1; else c[(i[0]+8) % 9] -= 1;
            for ( n=0; n<=9; n++ ) {
             if ( i[n] != c[n] )
              goto get_out;
            }
            printf( "matching number is %u%u%u%u%u%u%u%u%u%u\n", i[0],i[1],i[2],i[3],i[4],i[5],i[6],i[7],i[8],i[9] );
get_out:
            n=0;
           }
          }
         }
        }
       }
      }
     }
    }
   }
  }

}
