cmake_minimum_required(VERSION 2.8.7)
include_directories(${AIBATTLE_SOURCE_DIR}/Execution)
link_directories(${AIBATTLE_BINARY_DIR}/Execution)

add_executable(Tester tester.cpp)
add_executable(BattleShipsBadSample battleShipsBadSample.cpp maxitBadSimple.cpp)
add_executable(MaxitBadSample maxitBadSimple.cpp)

target_link_libraries(Tester execution)
