; Oliver Wong
; 50975
; Riche
; Final Project
; 5-2-02
; 
; emulator.asm

; ***************  Comments  ***************
; SLiC emulator for ADD, MV, AND, NOT, SETI, HALT
; Goes through a "case statment" which sends SLiC command
; to another part of program. Executes SLiC command
; in LC-2, then increments SPC and goes to next line of
; SLiC code
; ******************************************

	.ORIG	$1000		; directive: put code at start of user memory

	LD R0, Zero		; SR0 (zero out all registers initially)
	LD R1, Zero		; SR1
	LD R2, Zero		; SR2
	LD R3, Zero		; SR3
	LD R5, SMEM		; SPC (start SPC at xF000)

MAIN
	LD R6, FINISHMASK	; clear upper 8 bits of SREGs
	AND R0, R0, R6
	AND R1, R1, R6
	AND R2, R2, R6
	AND R3, R3, R6

	LDR R4, R5, #0		; HALT START
	LD R6, FOURMASK
	AND R4, R6, R4		; get OP code
	BRz PREADDFUNC		; ADD START/END
	NOT R6, R6		
	ADD R6, R6, #1
	ADD R4, R4, R6		; test if HALT
	BRz FINISHED		; HALT END

	LDR R4, R5, #0		; AND START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	LD R7, NEG32
	ADD R6, R6, R7		; test if AND
	BRz PREANDFUNC		; AND END

	LDR R4, R5, #0		; NOT START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	LD R7, NEG64
	ADD R6, R6, R7		; test if NOT
	BRz PRENOTFUNC		; NOT END

	LDR R4, R5, #0		; JMP START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	LD R7, NEG224
	ADD R6, R6, R7		; test if JMP
	BRz PREJMPFUNC		; JMP END


	LDR R4, R5, #0		; MV START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	ADD R6, R6, #-16	; test if MV
	BRz PREMVFUNC		; MV END

	LDR R4, R5, #0		; LD START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	LD R7, NEG64
	ADD R7, R7, R7		; R7 = -128
	ADD R6, R6, R7		; test if LD
	BRz PRELDFUNC		; LD END

	LDR R4, R5, #0		; ST START
	LD R6, FOURMASK
	AND R6, R6, R4		; get OP code
	LD R7, NEG64
	ADD R7, R7, R7		; R7 = -128
	ADD R7, R7, #-16	; R7 = -144
	ADD R6, R6, R7		; test if ST
	BRz PRESTFUNC		; ST END

	LDR R4, R5, #0		; LEA START
	LD R6, THREEMASK
	AND R4, R6, R4		; get OP code
	LD R6, NEG64
	LD R7, NEG32
	ADD R6, R6, R6		; R6 = -128
	ADD R6, R7, R6		; R6 = -160
	ADD R6, R4, R6		; test if LEA
	BRz PRELEAFUNC		; LEA END

	LDR R4, R5, #0		; BRR START
	LD R6, THREEMASK
	AND R4, R6, R4		; get OP code
	LD R7, NEG64
	ADD R6, R7, R7		; R6 = -128
	ADD R6, R7, R6		; R6 = -192
	ADD R6, R4, R6		; test if BRR
	BRz PREBRRFUNC		; BRR END

	LDR R4, R5, #0		; SETI START
	LD R6, THREEMASK
	AND R4, R6, R4		; get OP code
	LD R6, NEG64
	LD R7, NEG32
	ADD R6, R6, R7		; R6 = 96
	ADD R6, R4, R6		; test if SETI
	BRz PRESETIFUNC		; SETI END


GONEXT
	ADD R5, R5, #1		; increment SPC
	BR MAIN			; get next instruction at SPC

FINISHED			; HALT has been issued
	LD R6, FINISHMASK	
	AND R5, R5, R6		; clear upper 8 bits of SPC
	ADD R5, R5, #1		; increment SPC once more
	LD R6, SMEMREG		
	STR R0, R6, #0		; save SREGs
	STR R1, R6, #1
	STR R2, R6, #2
	STR R3, R6, #3
	STR R5, R6, #4		; save SPC
	HALT

PREANDFUNC
	JSR ANDTHING		; execute AND operation
	BR GONEXT		; get next SLiC command

PREJMPFUNC
	JSR JMPTHING
	BR GONEXT		; get next SLiC command

PREADDFUNC
	LDR R4, R5, #0		; reload SLiC command in R4
	JSR ADDTHING		; execute ADD
	BR GONEXT		; get next command

PREMVFUNC
	JSR MVTHING		; execute MV
	BR GONEXT		; get next command

PRENOTFUNC
	JSR NOTTHING		; execute NOT
	BR GONEXT		; get next command

PRESETIFUNC
	LDR R4, R5, #0		; reload R4, since "case" statement for SETI killed it
	JSR SETITHING		; execute SETI
	BR GONEXT		; get next command

PRELEAFUNC
	LDR R4, R5, #0		; reload R4, since "case" statement for LEA killed it
	JSR LEATHING		; execute LEA
	BR GONEXT		; get next command

PREBRRFUNC
	LDR R4, R5, #0		; reload R4, since "case" statement for LEA killed it
	JSR BRRTHING		; execute BRR
	BR GONEXT		; get next command

PRELDFUNC
	JSR LDTHING		; execute LD
	BR GONEXT		; get next command

PRESTFUNC
	JSR STTHING		; execute ST
	BR GONEXT		; get next command

GETBITS32			;MEM+#10 <- R4[3:2] shifted over 7 (R6 is overwritten)
	LD R6, MEM
	STR R0, R6, #0
	STR R1, R6, #1
	STR R2, R6, #2
	STR R5, R6, #5
	
	LD R0, MASK32
	AND R1 R0, R4		;get R4[3:2] only in R1
	AND R0, R0, #0
	ADD R0, R0, #7
LOOP32				;shift over 7 times
	ADD R1, R1, R1		;shift to the left
	ADD R0, R0, #-1
	BRp LOOP32

FINISH32
	STR R1, R6, #10		; return answer to MEM+10
	LDR R0, R6, #0		; restore registers
	LDR R1, R6, #1
	LDR R2, R6, #2
	LDR R5, R6, #5
	RET

GETBITS32B			;MEM+#10 <- R4[3:2] shifted over 4 (R6 is overwritten)
	LD R6, MEM
	STR R0, R6, #0
	STR R1, R6, #1
	STR R2, R6, #2
	STR R5, R6, #5
	
	LD R0, MASK32
	AND R1 R0, R4		;get R4[3:2] only in R1
	AND R0, R0, #0
	ADD R0, R0, #4
LOOP32B				;shift over 4 times
	ADD R1, R1, R1		;shift to the left
	ADD R0, R0, #-1
	BRp LOOP32B

FINISH32B
	STR R1, R6, #10		; put answer in MEM+10
	LDR R0, R6, #0		; restore registers
	LDR R1, R6, #1
	LDR R2, R6, #2
	LDR R5, R6, #5
	RET


GETBITS10			;MEM+#10 <- R4[1:0] (R6 is overwritten)
	LD R6, MEM
	STR R0, R6, #0		; save registers
	STR R5, R6, #5
	LD R0, MASK10
	AND R5, R0, R4
	STR R5, R6, #10		; put answer in MEM+10
	LDR R0, R6, #0		; restore registers
	LDR R5, R6, #5
	RET

GETBITS40			;MEM+#10 <- R4[4:0] (R6 is overwritten)
	LD R6, MEM
	STR R0, R6, #0		; save registers
	STR R5, R6, #5
	LD R0, MASK40
	AND R5, R0, R4
	STR R5, R6, #10		; put answer in MEM+10
	LDR R0, R6, #0		; restore registers
	LDR R5, R6, #5
	RET

SETITHING
	LD R6, MEM2
	STR R5, R6, #5		; save registers
	STR R7, R6, #7
	
	LD R5, ADDBASIC2	; load basic ADD LC-2 command
	JSR GETBITS40
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	LEA R6, SETILINE
	STR R5, R6, #0		; put ADD R0, R0, (#)R4[4:0] in SETILINE
	AND R0, R0, #0
SETILINE	.FILL $0000
	LD R6, MEM2
	LDR R5, R6, #5		; restore registers
	LDR R7, R6, #7		
	RET

LEATHING			; take current SPC and add IMM5 to it
	LD R6, MEM2
	STR R5, R6, #5		; save registers
	STR R7, R6, #7
	
	LD R5, ADDBASIC		; R5 = "ADD R0, R0, R0"
	JSR GETBITS40
	LD R6, MEM
	LDR R7, R6, #10		; R7 = IMM(4:0) of R4
	ADD R5, R5, #7		; R5 = "ADD R0, R0, R7"
	LEA R6, LEALINE
	STR R5, R6, #0		; put ADD R0, R0, R7
	LD R6, MEM2
	LDR R0, R6, #5		; R0 = SPC
	LD R5, FINISHMASK
	AND R0, R0, R5		; clear top bits of R0
LEALINE		.FILL $0000
	LD R6, MEM2
	LDR R5, R6, #5		; restore registers
	LDR R7, R6, #7		
	RET

BRRTHING			; take current SPC and add IMM5 to it
	ADD R0, R0, #0
	BRz BRREND

	LD R6, MEM2
	STR R5, R6, #5		; save registers
	STR R7, R6, #7
	
	LD R5, BRRVALUE		; R5 = "ADD R5, R5, #0"
	JSR GETBITS40
	LD R6, MEM
	LDR R7, R6, #10		; R7 = IMM(4:0) of R4
	ADD R5, R5, R7		; R5 = "ADD R5, R5, IMM(4:0)"
	LD R6, MEM2
	LDR R7, R6, #5		; R7 = SPC
	ADD R6, R5, #0		; R6 = R5 = "ADD R5, R5, IMM(4:0)"
	ADD R5, R7, #0		; R5 = R7 = SPC
	LEA R7, BRRLINE
	STR R6, R7, #0
	
BRRLINE		.FILL $0000
	ADD R5, R5, #-1
	LD R6, MEM2
	STR R5, R6, #5
	LD R6, MEM2
	LDR R5, R6, #5		; restore registers
	LDR R7, R6, #7		
BRREND
	RET

JMPTHING			; take current SPC and add IMM5 to it
	LD R6, MEM2
	STR R5, R6, #5		; save registers
	STR R7, R6, #7
	
	LD R5, JMPVALUE		; R5 = "ADD R5, R0, #0"
	JSR GETBITS32B
	LD R6, MEM
	LDR R7, R6, #10		; R7 = RD
	ADD R5, R5, R7		; R5 = "ADD R5, RD, #0"
	LEA R7, JMPLINE
	STR R5, R7, #0
	
JMPLINE		.FILL $0000
	LD R6, SMEM
	ADD R5, R6, R5
	ADD R5, R5, #-1
	LD R6, MEM2
	STR R5, R6, #11		; new SPC is in MEM2 + 11
	
	AND R4, R4, #1
	BRz JMPDONE
	
	LD R6, MEM2
	LDR R5, R6, #5		; R5 = "old" SPC
	ADD R0, R5, #1		; R0 = R5 +1 = SPC +1	
	
JMPDONE
	LD R6, MEM2
	LDR R5, R6, #11		; get new SPC in R5
	STR R5, R6, #5		; store new SPC
	LDR R5, R6, #5		; restore registers
	LDR R7, R6, #7		
	RET

NOTTHING
	LD R6, MEM2
	STR R5, R6, #5		; save registers
	STR R7, R6, #7
	
	LD R5, NOTBASIC		; get basic LC-2 command
	JSR GETBITS32
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7		; add D register to basic command
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10
	AND R6, R6, #0
	ADD R6, R6, #6
LOOPNOT
	ADD R7, R7, R7
	ADD R6, R6, #-1
	BRp LOOPNOT
	ADD R5, R5, R7		; add S register to command
	LEA R6, NOTLINE
	STR R5, R6, #0		; write NOT command to NOTLINE

NOTLINE		.FILL	$0000	
	LD R6, MEM2
	LDR R5, R6, #5		; restore registers
	LDR R7, R6, #7	
	RET
	
ANDTHING
	LD R6, MEM2
	STR R5, R6, #5
	STR R7, R6, #7
	
	LD R5, ANDBASIC
	JSR GETBITS32
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	JSR GETBITS32B
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	LEA R6, ANDLINE
	STR R5, R6, #0
	
ANDLINE		.FILL	$0000	
	LD R6, MEM2
	LDR R5, R6, #5
	LDR R7, R6, #7	
	RET

MVTHING				;SLiC Command in R4 (R6 is overwritten)
	LD R6, MEM2
	STR R5, R6, #5
	STR R7, R6, #7
	
	LD R5, ADDBASIC
	JSR GETBITS32
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	LD R7, MVVALUE
	ADD R5, R5, R7
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7		; ADD DR, R7, SR
	AND R7, R7, #0		; R7 = 0
	LEA R6, MVLINE
	STR R5, R6, #0
	
MVLINE		.FILL	$0000	
	LD R6, MEM2
	LDR R5, R6, #5
	LDR R7, R6, #7	
	
	RET

LDTHING			;SLiC Command in R4 (R6 is overwritten)
	LD R6, MEM2
	STR R5, R6, #5
	STR R7, R6, #7
	
	LD R5, LDVALUE		; R5 = "ADD R7, R0, #0"
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10		; R7 = source register #
	AND R6, R6, #0
	ADD R6, R6, #6
LDLOOP
	ADD R7, R7, R7		;shift to the left 6 times
	ADD R6, R6, #-1
	BRp LDLOOP
	
	ADD R5, R5, R7		; R5 = "ADD R7, RS, #0"
	LEA R6, LDLINE
	STR R5, R6, #0
	
LDLINE		.FILL	$0000	; R7 = RS
	LD R5, FINISHMASK
	AND R5, R5, R7
	LD R6, SMEM		; R6 = $F000
	ADD R5, R6, R5		; R5 = $F000 + RS
	JSR GETBITS32
	LD R6, MEM
	LDR R6, R6, #10
	ADD R7, R5, #0		; R7 = R5
	LD R5, LDVALUE2		; R5 = "LDR R0, R7, #0"
	ADD R5, R5, R6		; R5 = "LDR RD, R7, #0"
	LEA R6, LDLINE2
	STR R5, R6, #0

LDLINE2		.FILL 	$0000
	LD R6, MEM2
	LDR R5, R6, #5
	LDR R7, R6, #7	
	RET

STTHING			;SLiC Command in R4 (R6 is overwritten)
	LD R6, MEM2
	STR R5, R6, #5
	STR R7, R6, #7
	
	LD R5, LDVALUE		; R5 = "ADD R7, R0, #0"
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10		; R7 = source register #
	AND R6, R6, #0
	ADD R6, R6, #6
STLOOP
	ADD R7, R7, R7		;shift to the left 6 times
	ADD R6, R6, #-1
	BRp STLOOP
	
	ADD R5, R5, R7		; R5 = "ADD R7, RS, #0"
	LEA R6, STLINE
	STR R5, R6, #0
	
STLINE		.FILL	$0000	; R7 = RS
	LD R5, FINISHMASK
	AND R5, R5, R7
	LD R6, SMEM		; R6 = $F000
	ADD R5, R6, R5		; R7 = $F000 + RS
	JSR GETBITS32
	LD R6, MEM
	LDR R6, R6, #10
	ADD R7, R5, #0		; R7 = R5
	LD R5, STVALUE		; R5 = "STR R0, R7, #0"
	ADD R5, R5, R6		; R5 = "STR RD, R7, #0"
	LEA R6, STLINE2
	STR R5, R6, #0

STLINE2		.FILL 	$0000
	LD R6, MEM2
	LDR R5, R6, #5
	LDR R7, R6, #7	
	RET

ADDTHING			;SLiC Command in R4 (R6 is overwritten)
	LD R6, MEM2
	STR R5, R6, #5
	STR R7, R6, #7
	
	LD R5, ADDBASIC
	JSR GETBITS32
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	JSR GETBITS32B
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	JSR GETBITS10
	LD R6, MEM
	LDR R7, R6, #10
	ADD R5, R5, R7
	LEA R6, ADDLINE
	STR R5, R6, #0
	
ADDLINE		.FILL	$0000	
	LD R6, MEM2
	LDR R5, R6, #5
	LDR R7, R6, #7	
	RET
	
; Memory fills
JMPVALUE		.FILL	$1A20	; ADD R5, R0, #0
BRRVALUE		.FILL	$1B60	; ADD R5, R5, #0
LDVALUE			.FILL	$1E20	; ADD R7, R0, #0
LDVALUE2		.FILL	$61C0	; LDR R0, R7, #0
STVALUE			.FILL	$71C0	; STR R0, R7, #0
LDRBASIC		.FILL	$6000
ADDBASIC		.FILL	$1000
ADDBASIC2		.FILL	$1020
MVVALUE			.FILL	$01C0
ANDBASIC		.FILL	$5000
NOTBASIC		.FILL	$903F

Zero			.FILL	#0
NEG64			.FILL	#-64
NEG32			.FILL	#-32
NEG224			.FILL	#-224

SMEM			.FILL	$F000
SMEMREG			.FILL	$F100
MEM			.FILL	$5000
MEM2			.FILL	$5500

FOURMASK		.FILL	$00F0
THREEMASK		.FILL	$00E0
MASK32			.FILL	$000C
MASK10			.FILL	$0003
MASK40			.FILL	$001F
FINISHMASK		.FILL	$00FF

		.END		; directive: no more code