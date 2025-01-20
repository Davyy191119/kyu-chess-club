import React, { useState, useEffect } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Settings2, RotateCcw, Undo2, FastForward, Sun, Moon, Download } from 'lucide-react';

const ModernChessGUI = () => {
  const [theme, setTheme] = useState('light');
  const [position, setPosition] = useState(getInitialPosition());
  const [selectedSquare, setSelectedSquare] = useState(null);
  const [moveHistory, setMoveHistory] = useState([]);
  const [isDragging, setIsDragging] = useState(false);
  const [draggedPiece, setDraggedPiece] = useState(null);
  const [legalMoves, setLegalMoves] = useState([]);

  // Theme-based styles
  const themes = {
    light: {
      light: 'bg-amber-50',
      dark: 'bg-amber-200',
      piece: 'text-gray-800',
      text: 'text-gray-800',
      border: 'border-amber-300',
      hover: 'hover:bg-amber-100'
    },
    dark: {
      light: 'bg-gray-700',
      dark: 'bg-gray-800',
      piece: 'text-white',
      text: 'text-white',
      border: 'border-gray-600',
      hover: 'hover:bg-gray-600'
    }
  };

  function getInitialPosition() {
    return {
      'a8': '♜', 'b8': '♞', 'c8': '♝', 'd8': '♛', 'e8': '♚', 'f8': '♝', 'g8': '♞', 'h8': '♜',
      'a7': '♟', 'b7': '♟', 'c7': '♟', 'd7': '♟', 'e7': '♟', 'f7': '♟', 'g7': '♟', 'h7': '♟',
      'a2': '♙', 'b2': '♙', 'c2': '♙', 'd2': '♙', 'e2': '♙', 'f2': '♙', 'g2': '♙', 'h2': '♙',
      'a1': '♖', 'b1': '♘', 'c1': '♗', 'd1': '♕', 'e1': '♔', 'f1': '♗', 'g1': '♘', 'h1': '♖'
    };
  }

  const files = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
  const ranks = ['8', '7', '6', '5', '4', '3', '2', '1'];

  const handleDragStart = (e, square) => {
    if (position[square]) {
      setIsDragging(true);
      setDraggedPiece({ square, piece: position[square] });
      setSelectedSquare(square);
      // In a real implementation, calculate legal moves here
      setLegalMoves(['e4', 'e3', 'e2']); // Example legal moves
    }
  };

  const handleDragOver = (e) => {
    e.preventDefault();
  };

  const handleDrop = (e, targetSquare) => {
    e.preventDefault();
    if (draggedPiece && draggedPiece.square !== targetSquare) {
      // In a real implementation, validate move with chess engine
      const newPosition = { ...position };
      delete newPosition[draggedPiece.square];
      newPosition[targetSquare] = draggedPiece.piece;
      setPosition(newPosition);
      setMoveHistory([...moveHistory, `${draggedPiece.piece} ${draggedPiece.square}-${targetSquare}`]);
    }
    setIsDragging(false);
    setDraggedPiece(null);
    setSelectedSquare(null);
    setLegalMoves([]);
  };

  const undoLastMove = () => {
    if (moveHistory.length > 0) {
      // In a real implementation, revert to previous position
      setMoveHistory(moveHistory.slice(0, -1));
    }
  };

  const requestEngineSuggestion = () => {
    // In a real implementation, request move from Stockfish
    console.log('Requesting engine suggestion...');
  };

  return (
    <div className={`w-full max-w-6xl mx-auto p-4 ${theme === 'dark' ? 'bg-gray-900' : 'bg-white'}`}>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {/* Left sidebar - Game controls */}
        <Card className={`lg:col-span-1 ${theme === 'dark' ? 'bg-gray-800 text-white' : ''}`}>
          <CardHeader>
            <CardTitle>Game Controls</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="flex flex-wrap gap-2">
              <Button variant="outline" size="icon" onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}>
                {theme === 'light' ? <Moon className="h-4 w-4" /> : <Sun className="h-4 w-4" />}
              </Button>
              <Button variant="outline" size="icon" onClick={() => setPosition(getInitialPosition())}>
                <RotateCcw className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon" onClick={undoLastMove}>
                <Undo2 className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon" onClick={requestEngineSuggestion}>
                <FastForward className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon">
                <Settings2 className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon">
                <Download className="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Main chessboard */}
        <Card className={`lg:col-span-1 ${theme === 'dark' ? 'bg-gray-800' : ''}`}>
          <CardContent className="p-4">
            <div className={`grid grid-cols-8 gap-0 border ${themes[theme].border}`}>
              {ranks.map((rank) => (
                files.map((file) => {
                  const square = `${file}${rank}`;
                  const isLight = (files.indexOf(file) + ranks.indexOf(rank)) % 2 === 0;
                  const isSelected = selectedSquare === square;
                  const isLegalMove = legalMoves.includes(square);
                  
                  return (
                    <div
                      key={square}
                      draggable={!!position[square]}
                      onDragStart={(e) => handleDragStart(e, square)}
                      onDragOver={handleDragOver}
                      onDrop={(e) => handleDrop(e, square)}
                      className={`
                        aspect-square flex items-center justify-center text-3xl
                        ${isLight ? themes[theme].light : themes[theme].dark}
                        ${isSelected ? 'ring-2 ring-blue-500' : ''}
                        ${isLegalMove ? 'ring-2 ring-green-500' : ''}
                        ${themes[theme].hover}
                        cursor-pointer
                        transition-colors
                      `}
                    >
                      <span className={themes[theme].piece}>
                        {position[square] || ''}
                      </span>
                    </div>
                  );
                })
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Right sidebar - Move history */}
        <Card className={`lg:col-span-1 ${theme === 'dark' ? 'bg-gray-800 text-white' : ''}`}>
          <CardHeader>
            <CardTitle>Move History</CardTitle>
          </CardHeader>
          <CardContent className="h-96 overflow-y-auto">
            <div className="space-y-1">
              {moveHistory.map((move, index) => (
                <div
                  key={index}
                  className={`p-2 rounded ${themes[theme].hover} ${index % 2 === 0 ? themes[theme].light : ''}`}
                >
                  {`${Math.floor(index/2) + 1}. ${move}`}
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default ModernChessGUI;