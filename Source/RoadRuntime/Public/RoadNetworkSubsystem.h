#pragma once

#include "CoreMinimal.h"
#include "Subsystems/WorldSubsystem.h"
#include "RoadNetworkSubsystem.generated.h"

class ARoadNode;
class ARoadSegment;

UCLASS()
class URoadNetworkSubsystem : public UWorldSubsystem
{
    GENERATED_BODY()

public:
    void RegisterNode(ARoadNode* Node);
    void UnregisterNode(ARoadNode* Node);
    void RegisterSegment(ARoadSegment* Segment);
    void UnregisterSegment(ARoadSegment* Segment);
    const TArray<TWeakObjectPtr<ARoadNode>>& GetNodes() const;
    const TArray<TWeakObjectPtr<ARoadSegment>>& GetSegments() const;

private:
    void CleanupInvalidEntries();

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadNode>> Nodes;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadSegment>> Segments;
};
